<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

class YearField extends Field
{
    public function isSortable(): bool
    {
        return true;
    }

    public function getCast(Imports $imports): ?string
    {
        return "'integer'";
    }

    protected function fakerCall(): string
    {
        return 'numberBetween(1990, 2030)';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\TextInput';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        return ['integer()', 'minValue(1901)', 'maxValue(2155)'];
    }

    public function getFilter(Imports $imports): ?string
    {
        $class = $imports->add('Javaabu\\Filters\\NumberRangeFilter');

        return "{$class}::make('{$this->getName()}')->label({$this->getLabelCall()})->integer()";
    }

    public function getTypeScriptType(): string
    {
        return 'number';
    }

    public function getValidValue(): string
    {
        return '2024';
    }

    public function getOtherValidValue(): string
    {
        return '2025';
    }

    public function getInvalidValue(): ?string
    {
        return '1800';
    }
}
