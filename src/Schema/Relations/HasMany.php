<?php

namespace Javaabu\Generators\Schema\Relations;

use Illuminate\Support\Str;

class HasMany extends Relation
{
    public function __construct(
        string $name,
        string $related_table,
        public readonly string $foreign_key,
        public readonly string $parent_table,
        public readonly string $local_key = 'id',
    ) {
        parent::__construct($name, $related_table);
    }

    public function type(): string
    {
        return 'HasMany';
    }

    public function definition(): string
    {
        $arguments = ["{$this->relatedModel()}::class"];

        $conventional_key = Str::singular($this->parent_table) . '_id';

        if ($this->foreign_key !== $conventional_key || $this->local_key !== 'id') {
            $arguments[] = "'{$this->foreign_key}'";
        }

        if ($this->local_key !== 'id') {
            $arguments[] = "'{$this->local_key}'";
        }

        return 'hasMany(' . implode(', ', $arguments) . ')';
    }
}
