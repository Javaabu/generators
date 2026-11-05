<?php

namespace Javaabu\Generators\Schema\Relations;

use Javaabu\GeneratorHelpers\StringCaser;

abstract class Relation
{
    public function __construct(
        public readonly string $name,
        public readonly string $related_table,
    ) {}

    public function relatedModel(): string
    {
        return StringCaser::singularStudly($this->related_table);
    }

    abstract public function type(): string;

    /**
     * The relation method body, e.g. belongsTo(Category::class).
     */
    abstract public function definition(): string;
}
