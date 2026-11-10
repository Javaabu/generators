<?php

namespace Javaabu\Generators\Fields;

use Illuminate\Support\Str;
use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Naming;

/**
 * A belongsTo foreign key, edited with a select of the related records.
 */
class ForeignKeyField extends Field
{
    public function __construct(
        string $name,
        protected string $related_table,
        protected string $related_key = 'id',
        bool $nullable = false,
        mixed $default = null,
        bool $unique = false,
        protected string $related_name_field = 'name',
        protected string $models_namespace = 'App\\Models',
    ) {
        parent::__construct($name, $nullable, $default, $unique);
    }

    public function getRelatedTable(): string
    {
        return $this->related_table;
    }

    public function getRelatedKey(): string
    {
        return $this->related_key;
    }

    public function getRelatedNameField(): string
    {
        return $this->related_name_field;
    }

    public function getRelatedModel(): string
    {
        return $this->models_namespace . '\\' . StringCaser::singularStudly($this->related_table);
    }

    public function getRelationName(): string
    {
        return Naming::belongsTo($this->getName(), $this->related_table);
    }

    public function getDisplayName(): string
    {
        return Str::snake($this->getRelationName());
    }

    public function isSortable(): bool
    {
        return false;
    }

    protected function fakerCall(): string
    {
        return '';
    }

    public function getFactoryValue(Imports $imports): string
    {
        $model = $imports->add($this->getRelatedModel());

        $factory = $this->related_key === 'id'
            ? "{$model}::factory()"
            : "fn () => {$model}::factory()->create()->{$this->related_key}";

        if ($this->isNullable()) {
            return $this->related_key === 'id'
                ? "fn () => fake()->boolean() ? {$model}::factory() : null"
                : "fn () => fake()->boolean() ? {$model}::factory()->create()->{$this->related_key} : null";
        }

        return $factory;
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\Select';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        return ["relationship('{$this->getRelationName()}', '{$this->related_name_field}')", 'searchable()'];
    }

    public function getFilter(Imports $imports): ?string
    {
        $class = $imports->add('Javaabu\\Filters\\SelectFilter');

        return "{$class}::make('{$this->getName()}')->label({$this->getLabelCall()})->relationship('{$this->getRelationName()}', '{$this->related_name_field}')->searchable()";
    }

    public function getBladeCell(string $variable): string
    {
        return '<x-forms::table.cell :label="' . $this->getLabelCall() . '" :value="' . $variable . '->' . $this->getRelationName() . '?->' . $this->related_name_field . '" />';
    }

    public function getBladeEntry(string $variable): string
    {
        return '<x-forms::text-entry :label="' . $this->getLabelCall() . '" :value="' . $variable . '->' . $this->getRelationName() . '?->' . $this->related_name_field . '" />';
    }

    public function getReactCell(string $variable): string
    {
        return '<TableCell label={__("' . $this->escape($this->getLabel(), '"') . '")} value={' . $variable . '.' . Str::snake($this->getRelationName()) . '?.' . $this->related_name_field . ' ?? "-"} />';
    }

    public function getReactEntry(string $variable): string
    {
        return '<TextEntry label={__("' . $this->escape($this->getLabel(), '"') . '")} value={' . $variable . '.' . Str::snake($this->getRelationName()) . '?.' . $this->related_name_field . ' ?? "-"} />';
    }

    public function getTypeScriptType(): string
    {
        return $this->related_key === 'id' ? 'number' : 'string';
    }

    public function getValidValue(): string
    {
        return '$' . Str::snake($this->getRelationName()) . '->' . $this->related_key;
    }

    public function getOtherValidValue(): string
    {
        return '$other_' . Str::snake($this->getRelationName()) . '->' . $this->related_key;
    }

    public function getInvalidValue(): ?string
    {
        return $this->related_key === 'id' ? '-1' : "'ބަދަލު'";
    }

    public function getTestSetup(Imports $imports, string $prefix): array
    {
        $model = $imports->add($this->getRelatedModel());
        $variable = Str::snake($this->getRelationName());

        return [
            "\${$variable} = {$model}::factory()->create();",
            "\$other_{$variable} = {$model}::factory()->create();",
        ];
    }
}
