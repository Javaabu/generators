<?php

namespace Javaabu\Generators\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
use Javaabu\Generators\Exceptions\TableDoesNotExistException;
use Javaabu\Generators\Exceptions\UnsupportedDbDriverException;

/**
 * Reads tables through Laravel's schema builder, so it works on MySQL,
 * MariaDB, PostgreSQL and SQLite, and normalizes the column types.
 */
class SchemaInspector
{
    /** @var array<string, Table> */
    protected array $tables = [];

    /** @var array<int, string>|null */
    protected ?array $table_names = null;

    public function __construct(
        protected DatabaseManager $db,
        protected ?string $connection = null,
    ) {}

    public function connection(): Connection
    {
        return $this->db->connection($this->connection);
    }

    public function driver(): string
    {
        return $this->connection()->getDriverName();
    }

    public function hasTable(string $table): bool
    {
        return in_array($table, $this->tableNames(), true);
    }

    /**
     * Every table in the database, without the framework's own tables.
     *
     * @return array<int, string>
     */
    public function tableNames(): array
    {
        if ($this->table_names !== null) {
            return $this->table_names;
        }

        $schema = $this->connection()->getSchemaBuilder();
        $tables = $schema->getTables($this->schemaName());

        $prefix = $this->connection()->getTablePrefix();

        $names = [];

        foreach ($tables as $table) {
            $name = $table['name'];

            if ($prefix && str_starts_with($name, $prefix)) {
                $name = substr($name, strlen($prefix));
            }

            if (str_starts_with($name, 'sqlite_')) {
                continue;
            }

            $names[] = $name;
        }

        sort($names);

        return $this->table_names = array_values(array_unique($names));
    }

    public function table(string $name): Table
    {
        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        }

        if (! in_array($this->driver(), ['mysql', 'mariadb', 'pgsql', 'sqlite'], true)) {
            throw new UnsupportedDbDriverException("This database driver is not supported: {$this->driver()}");
        }

        $schema = $this->connection()->getSchemaBuilder();

        if (! $schema->hasTable($name)) {
            throw new TableDoesNotExistException("Table '{$name}' not found!");
        }

        $indexes = $schema->getIndexes($name);

        $unique_columns = [];
        $primary_key = [];

        foreach ($indexes as $index) {
            if ($index['primary']) {
                $primary_key = $index['columns'];

                continue;
            }

            if ($index['unique'] && count($index['columns']) === 1) {
                $unique_columns[] = $index['columns'][0];
            }
        }

        $check_options = $this->checkConstraintOptions($name);

        $columns = [];

        foreach ($schema->getColumns($name) as $definition) {
            $column = $this->normalize($definition, in_array($definition['name'], $unique_columns, true), $check_options);

            $columns[$column->name] = $column;
        }

        $foreign_keys = [];

        foreach ($schema->getForeignKeys($name) as $foreign_key) {
            if (count($foreign_key['columns']) !== 1) {
                continue;
            }

            $foreign_table = $foreign_key['foreign_table'];
            $prefix = $this->connection()->getTablePrefix();

            if ($prefix && str_starts_with($foreign_table, $prefix)) {
                $foreign_table = substr($foreign_table, strlen($prefix));
            }

            $foreign_keys[] = new ForeignKey(
                $foreign_key['columns'][0],
                $foreign_table,
                $foreign_key['foreign_columns'][0] ?? 'id',
                isset($foreign_key['on_delete']) ? strtolower((string) $foreign_key['on_delete']) : null,
            );
        }

        return $this->tables[$name] = new Table($name, $columns, $foreign_keys, $primary_key);
    }

    /**
     * Forget the tables read so far.
     */
    public function flush(): void
    {
        $this->tables = [];
        $this->table_names = null;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, array<int, string>>  $check_options
     */
    protected function normalize(array $definition, bool $unique, array $check_options): Column
    {
        $name = $definition['name'];
        $type_name = strtolower((string) $definition['type_name']);
        $full_type = strtolower((string) $definition['type']);

        [$type, $length, $precision, $scale, $options] = $this->resolveType($type_name, $full_type);

        if (isset($check_options[$name]) && in_array($type, ['string', 'text'], true)) {
            $type = 'enum';
            $options = $check_options[$name];
        }

        return new Column(
            name: $name,
            type: $type,
            full_type: $full_type,
            nullable: (bool) $definition['nullable'],
            default: $this->normalizeDefault($definition['default'] ?? null),
            auto_increment: (bool) $definition['auto_increment'],
            unique: $unique,
            comment: ($definition['comment'] ?? null) ?: null,
            length: $length,
            precision: $precision,
            scale: $scale,
            unsigned: str_contains($full_type, 'unsigned'),
            options: $options,
        );
    }

    /**
     * @return array{0: string, 1: ?int, 2: ?int, 3: ?int, 4: array<int, string>}
     */
    protected function resolveType(string $type_name, string $full_type): array
    {
        $arguments = $this->typeArguments($full_type);

        if ($type_name === 'enum' || $type_name === 'set') {
            preg_match_all("/'((?:[^']|'')*)'/", $full_type, $matches);

            return ['enum', null, null, null, array_map(fn (string $option) => str_replace("''", "'", $option), $matches[1])];
        }

        if ($type_name === 'tinyint' && str_starts_with($full_type, 'tinyint(1)') && config('generators.tinyint1_to_bool', true)) {
            return ['boolean', null, null, null, []];
        }

        if (in_array($type_name, ['bool', 'boolean'], true)) {
            return ['boolean', null, null, null, []];
        }

        if (in_array($type_name, ['varchar', 'char', 'bpchar', 'character varying', 'character', 'nvarchar', 'nchar', 'string'], true)) {
            return ['string', $arguments[0] ?? 255, null, null, []];
        }

        if (in_array($type_name, ['tinytext', 'text', 'mediumtext', 'longtext', 'clob'], true)) {
            $lengths = ['tinytext' => 255, 'text' => 65535, 'mediumtext' => 16777215, 'longtext' => 4294967295];

            return ['text', $lengths[$type_name] ?? null, null, null, []];
        }

        if (in_array($type_name, ['json', 'jsonb'], true)) {
            return ['json', null, null, null, []];
        }

        if (in_array($type_name, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'int2', 'int4', 'int8', 'serial', 'bigserial', 'smallserial'], true)) {
            $aliases = [
                'int2' => 'smallint',
                'int4' => 'int',
                'integer' => 'int',
                'serial' => 'int',
                'int8' => 'bigint',
                'bigserial' => 'bigint',
                'smallserial' => 'smallint',
            ];

            return [$aliases[$type_name] ?? $type_name, null, null, null, []];
        }

        if (in_array($type_name, ['decimal', 'numeric', 'double', 'float', 'real', 'float4', 'float8', 'double precision'], true)) {
            $precision = $arguments[0] ?? null;
            $scale = $arguments[1] ?? null;

            if ($precision === null) {
                // SQLite and floats keep no precision
                $precision = 10;
                $scale = 2;
            }

            return ['decimal', null, $precision, $scale ?? 0, []];
        }

        if ($type_name === 'year') {
            return ['year', null, null, null, []];
        }

        if (in_array($type_name, ['datetime', 'timestamp', 'timestamptz', 'datetime2', 'datetimeoffset'], true)) {
            return [$type_name === 'datetime' ? 'datetime' : 'timestamp', null, null, null, []];
        }

        if ($type_name === 'date') {
            return ['date', null, null, null, []];
        }

        if (in_array($type_name, ['time', 'timetz'], true)) {
            return ['time', null, null, null, []];
        }

        if ($type_name === 'uuid') {
            return ['uuid', 36, null, null, []];
        }

        return [$type_name, null, null, null, []];
    }

    /**
     * The numbers in a type's parentheses: decimal(14,2) gives [14, 2].
     *
     * @return array<int, int>
     */
    protected function typeArguments(string $full_type): array
    {
        if (! preg_match('/\(([\d\s,]+)\)/', $full_type, $matches)) {
            return [];
        }

        return array_map('intval', array_map('trim', explode(',', $matches[1])));
    }

    protected function normalizeDefault(mixed $default): mixed
    {
        if ($default === null) {
            return null;
        }

        if (! is_string($default)) {
            return $default;
        }

        $value = $default;

        // PostgreSQL: 'draft'::character varying
        if (preg_match("/^'(.*)'::[\w\s]+$/s", $value, $matches)) {
            return str_replace("''", "'", $matches[1]);
        }

        if (preg_match("/^'(.*)'$/s", $value, $matches)) {
            return str_replace("''", "'", $matches[1]);
        }

        if (strtoupper($value) === 'NULL') {
            return null;
        }

        return $value;
    }

    /**
     * Options of enum columns that the driver stores as a check constraint.
     *
     * @return array<string, array<int, string>>
     */
    protected function checkConstraintOptions(string $table): array
    {
        return match ($this->driver()) {
            'sqlite' => $this->sqliteCheckOptions($table),
            'pgsql' => $this->postgresCheckOptions($table),
            default => [],
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function sqliteCheckOptions(string $table): array
    {
        $prefixed = $this->connection()->getTablePrefix() . $table;

        $row = $this->connection()->selectOne("select sql from sqlite_master where type = 'table' and name = ?", [$prefixed]);

        if (! $row || ! $row->sql) {
            return [];
        }

        preg_match_all('/check\s*\(\s*["`]?(\w+)["`]?\s+in\s*\((.*?)\)\s*\)/is', $row->sql, $matches, PREG_SET_ORDER);

        $options = [];

        foreach ($matches as $match) {
            preg_match_all("/'((?:[^']|'')*)'/", $match[2], $values);

            $options[$match[1]] = array_map(fn (string $option) => str_replace("''", "'", $option), $values[1]);
        }

        return $options;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function postgresCheckOptions(string $table): array
    {
        $prefixed = $this->connection()->getTablePrefix() . $table;

        $rows = $this->connection()->select(
            "select pg_get_constraintdef(c.oid) as definition
            from pg_constraint c
            join pg_class t on c.conrelid = t.oid
            join pg_namespace n on n.oid = t.relnamespace
            where c.contype = 'c' and t.relname = ? and n.nspname = current_schema()",
            [$prefixed]
        );

        $options = [];

        foreach ($rows as $row) {
            $definition = (string) $row->definition;

            if (! Str::contains($definition, ['ANY', ' IN '])) {
                continue;
            }

            if (! preg_match('/\(+\s*"?(\w+)"?\)?(?:::\w+(?:\s\w+)?)?\s*(?:=\s*ANY|IN)/i', $definition, $column)) {
                continue;
            }

            preg_match_all("/'((?:[^']|'')*)'/", $definition, $values);

            $options[$column[1]] = array_map(fn (string $option) => str_replace("''", "'", $option), $values[1]);
        }

        return $options;
    }

    protected function schemaName(): ?string
    {
        return match ($this->driver()) {
            'mysql', 'mariadb' => $this->connection()->getDatabaseName(),
            default => null,
        };
    }
}
