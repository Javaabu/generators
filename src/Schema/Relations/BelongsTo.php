<?php

namespace Javaabu\Generators\Schema\Relations;

use Illuminate\Support\Str;

class BelongsTo extends Relation
{
    public function __construct(
        string $name,
        string $related_table,
        public readonly string $foreign_key,
        public readonly string $owner_key = 'id',
    ) {
        parent::__construct($name, $related_table);
    }

    public function type(): string
    {
        return 'BelongsTo';
    }

    public function definition(): string
    {
        $arguments = ["{$this->relatedModel()}::class"];

        $conventional_key = Str::snake($this->name) . '_id';

        if ($this->foreign_key !== $conventional_key || $this->owner_key !== 'id') {
            $arguments[] = "'{$this->foreign_key}'";
        }

        if ($this->owner_key !== 'id') {
            $arguments[] = "'{$this->owner_key}'";
        }

        return 'belongsTo(' . implode(', ', $arguments) . ')';
    }
}
