<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

class TimeField extends Field
{
    public function isSortable(): bool
    {
        return true;
    }

    protected function fakerCall(): string
    {
        return "time('H:i')";
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\TimePicker';
    }

    public function getValidValue(): string
    {
        return "'14:54'";
    }

    public function getOtherValidValue(): string
    {
        return "'09:30'";
    }

    public function getInvalidValue(): ?string
    {
        return "'ބަދަލު'";
    }

    public function getDatabaseValue(string $input): ?string
    {
        return null;
    }

    public function getSavedValueAssertion(string $variable, string $input, ?Imports $imports = null): ?string
    {
        return "\$this->assertEquals({$input}, substr((string) {$variable}->{$this->getName()}, 0, 5));";
    }
}
