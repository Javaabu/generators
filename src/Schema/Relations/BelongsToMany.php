<?php

namespace Javaabu\Generators\Schema\Relations;

use Illuminate\Support\Str;

class BelongsToMany extends Relation
{
    /**
     * @param  array<int, string>  $pivot_columns  extra pivot columns
     */
    public function __construct(
        string $name,
        string $related_table,
        public readonly string $pivot_table,
        public readonly string $foreign_pivot_key,
        public readonly string $related_pivot_key,
        public readonly string $parent_table,
        public readonly array $pivot_columns = [],
        public readonly bool $pivot_timestamps = false,
    ) {
        parent::__construct($name, $related_table);
    }

    public function type(): string
    {
        return 'BelongsToMany';
    }

    public function definition(): string
    {
        $arguments = ["{$this->relatedModel()}::class"];

        $tables = [Str::singular($this->parent_table), Str::singular($this->related_table)];
        sort($tables);

        $conventional_table = implode('_', $tables);
        $conventional_foreign = Str::singular($this->parent_table) . '_id';
        $conventional_related = Str::singular($this->related_table) . '_id';

        $keys_conventional = $this->foreign_pivot_key === $conventional_foreign
            && $this->related_pivot_key === $conventional_related;

        if ($this->pivot_table !== $conventional_table || ! $keys_conventional) {
            $arguments[] = "'{$this->pivot_table}'";
        }

        if (! $keys_conventional) {
            $arguments[] = "'{$this->foreign_pivot_key}'";
            $arguments[] = "'{$this->related_pivot_key}'";
        }

        $definition = 'belongsToMany(' . implode(', ', $arguments) . ')';

        if ($this->pivot_columns) {
            $definition .= "->withPivot('" . implode("', '", $this->pivot_columns) . "')";
        }

        if ($this->pivot_timestamps) {
            $definition .= '->withTimestamps()';
        }

        return $definition;
    }
}
