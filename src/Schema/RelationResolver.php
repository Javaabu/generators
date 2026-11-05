<?php

namespace Javaabu\Generators\Schema;

use Illuminate\Support\Str;
use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\Generators\Schema\Relations\BelongsTo;
use Javaabu\Generators\Schema\Relations\BelongsToMany;
use Javaabu\Generators\Schema\Relations\HasMany;
use Javaabu\Generators\Support\Naming;

/**
 * Finds a table's relations from the foreign keys in the whole schema:
 * belongsTo from its own keys, hasMany from keys on other tables that point
 * at it, and belongsToMany through pivot tables.
 */
class RelationResolver
{
    /** Columns a pivot table may have besides its two foreign keys. */
    protected const array PIVOT_BOOKKEEPING_COLUMNS = ['id', 'created_at', 'updated_at'];

    public function __construct(
        protected SchemaInspector $inspector,
    ) {}

    /**
     * @return array<int, BelongsTo>
     */
    public function belongsTo(Table $table): array
    {
        $relations = [];

        foreach ($table->foreign_keys as $foreign_key) {
            if ($this->isIgnored($foreign_key->foreign_table)) {
                continue;
            }

            $relations[] = new BelongsTo(
                Naming::belongsTo($foreign_key->column, $foreign_key->foreign_table),
                $foreign_key->foreign_table,
                $foreign_key->column,
                $foreign_key->foreign_column,
            );
        }

        return $relations;
    }

    /**
     * @return array<int, HasMany>
     */
    public function hasMany(Table $table): array
    {
        $relations = [];
        $names = [];

        foreach ($this->inspector->tableNames() as $other_name) {
            if ($other_name === $table->name || $this->isIgnored($other_name)) {
                continue;
            }

            $other = $this->inspector->table($other_name);

            if ($this->isPivotTable($other)) {
                continue;
            }

            $keys = array_values(array_filter(
                $other->foreign_keys,
                fn (ForeignKey $foreign_key) => $foreign_key->foreign_table === $table->name
            ));

            foreach ($keys as $foreign_key) {
                $name = StringCaser::pluralCamel($other_name);

                // a second key to the same table: name it after the key (e.g. reviewer_id gives reviewedPosts)
                if (count($keys) > 1 || in_array($name, $names, true)) {
                    $name = Str::camel(Str::snake(Naming::belongsTo($foreign_key->column, $table->name)) . '_' . $other_name);
                }

                $names[] = $name;

                $relations[] = new HasMany(
                    $name,
                    $other_name,
                    $foreign_key->column,
                    $table->name,
                    $foreign_key->foreign_column,
                );
            }
        }

        return $relations;
    }

    /**
     * @return array<int, BelongsToMany>
     */
    public function belongsToMany(Table $table): array
    {
        $relations = [];

        foreach ($this->inspector->tableNames() as $pivot_name) {
            if ($pivot_name === $table->name || $this->isIgnored($pivot_name)) {
                continue;
            }

            $pivot = $this->inspector->table($pivot_name);

            if (! $this->isPivotTable($pivot)) {
                continue;
            }

            [$first, $second] = $pivot->foreign_keys;

            foreach ([[$first, $second], [$second, $first]] as [$own, $other]) {
                if ($own->foreign_table !== $table->name) {
                    continue;
                }

                $relations[] = new BelongsToMany(
                    StringCaser::pluralCamel($other->foreign_table),
                    $other->foreign_table,
                    $pivot_name,
                    $own->column,
                    $other->column,
                    $table->name,
                    $this->pivotColumns($pivot),
                    $pivot->hasTimestamps(),
                );

                break;
            }
        }

        return $relations;
    }

    /**
     * A table with two foreign keys to two different tables, and no data
     * columns of its own except id and timestamps (or any other extra pivot
     * columns, when it is named after both tables).
     */
    public function isPivotTable(Table $table): bool
    {
        if (count($table->foreign_keys) !== 2) {
            return false;
        }

        [$first, $second] = $table->foreign_keys;

        if ($first->foreign_table === $second->foreign_table) {
            return false;
        }

        // Eloquent pivots join on the related tables' keys
        if (! $this->referencesKey($first) || ! $this->referencesKey($second)) {
            return false;
        }

        $extra = $this->pivotColumns($table);

        if (! $extra) {
            return true;
        }

        $tables = [Str::singular($first->foreign_table), Str::singular($second->foreign_table)];
        sort($tables);

        return $table->name === implode('_', $tables);
    }

    /**
     * @return array<int, string>
     */
    protected function pivotColumns(Table $table): array
    {
        $keys = array_map(fn (ForeignKey $foreign_key) => $foreign_key->column, $table->foreign_keys);

        return array_values(array_diff(array_keys($table->columns), $keys, self::PIVOT_BOOKKEEPING_COLUMNS));
    }

    protected function referencesKey(ForeignKey $foreign_key): bool
    {
        if (! $this->inspector->hasTable($foreign_key->foreign_table)) {
            return $foreign_key->foreign_column === 'id';
        }

        return $this->inspector->table($foreign_key->foreign_table)->keyName() === $foreign_key->foreign_column;
    }

    protected function isIgnored(string $table): bool
    {
        return in_array($table, (array) config('generators.ignored_tables', []), true);
    }
}
