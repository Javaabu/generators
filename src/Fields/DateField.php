<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

class DateField extends DateTimeField
{
    public function getCast(Imports $imports): ?string
    {
        return "'date'";
    }

    protected function fakerCall(): string
    {
        return 'date()';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\DatePicker';
    }

    public function getValidValue(): string
    {
        return "'2024-02-12'";
    }

    public function getOtherValidValue(): string
    {
        return "'2025-11-06'";
    }

    public function getSavedValueAssertion(string $variable, string $input): ?string
    {
        return "\$this->assertEquals({$input}, {$variable}->{$this->getName()}?->format('Y-m-d'));";
    }

    public function getResourceValue(): string
    {
        return '$this->' . $this->getName() . "?->format('Y-m-d')";
    }
}
