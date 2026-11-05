<?php

namespace Javaabu\Generators\Schema;

/**
 * A database table, read through the schema inspector.
 */
final readonly class Table
{
    /**
     * @param  array<string, Column>  $columns  keyed by name, in table order
     * @param  array<int, ForeignKey>  $foreign_keys  single-column foreign keys
     * @param  array<int, string>  $primary_key
     */
    public function __construct(
        public string $name,
        public array $columns,
        public array $foreign_keys = [],
        public array $primary_key = [],
    ) {}

    public function hasColumn(string $column): bool
    {
        return array_key_exists($column, $this->columns);
    }

    public function column(string $column): ?Column
    {
        return $this->columns[$column] ?? null;
    }

    public function foreignKeyFor(string $column): ?ForeignKey
    {
        foreach ($this->foreign_keys as $foreign_key) {
            if ($foreign_key->column === $column) {
                return $foreign_key;
            }
        }

        return null;
    }

    /**
     * The auto-incrementing key, or the single-column primary key.
     */
    public function keyName(): ?string
    {
        foreach ($this->columns as $column) {
            if ($column->auto_increment) {
                return $column->name;
            }
        }

        if (count($this->primary_key) === 1) {
            return $this->primary_key[0];
        }

        return null;
    }

    public function hasTimestamps(): bool
    {
        return $this->isDateTimeColumn('created_at') && $this->isDateTimeColumn('updated_at');
    }

    public function hasSoftDeletes(): bool
    {
        return $this->isDateTimeColumn('deleted_at');
    }

    /**
     * Whether the table has javaabu/translatable's JSON columns.
     */
    public function hasTranslationColumns(): bool
    {
        return $this->hasColumn('translations') && $this->hasColumn('lang');
    }

    protected function isDateTimeColumn(string $name): bool
    {
        $column = $this->column($name);

        return $column !== null && in_array($column->type, ['datetime', 'timestamp'], true);
    }
}
