<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

class DateTimeField extends Field
{
    public function isSortable(): bool
    {
        return true;
    }

    public function getCast(Imports $imports): ?string
    {
        return "'datetime'";
    }

    protected function fakerCall(): string
    {
        return 'dateTimeBetween(\'-1 year\', \'+1 year\')';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\DateTimePicker';
    }

    public function getFilter(Imports $imports): ?string
    {
        $class = $imports->add('Javaabu\\Filters\\DateRangeFilter');

        return "{$class}::make('{$this->getName()}')->label({$this->getLabelCall()})";
    }

    public function getValidValue(): string
    {
        return "'2024-02-12 14:54'";
    }

    public function getOtherValidValue(): string
    {
        return "'2025-11-06 09:30'";
    }

    public function getInvalidValue(): ?string
    {
        return "'ބަދަލު'";
    }

    public function getDatabaseValue(string $input): ?string
    {
        return null;
    }

    public function getSavedValueAssertion(string $variable, string $input): ?string
    {
        return "\$this->assertEquals({$input}, {$variable}->{$this->getName()}?->format('Y-m-d H:i'));";
    }

    public function getResourceValue(): string
    {
        return '$this->' . $this->getName() . "?->format('Y-m-d H:i')";
    }
}
