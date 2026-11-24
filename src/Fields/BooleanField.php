<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

class BooleanField extends Field
{
    public function isRequired(): bool
    {
        return false;
    }

    protected function canBeRequired(): bool
    {
        return false;
    }

    public function getCast(Imports $imports): ?string
    {
        return "'boolean'";
    }

    public function getFactoryValue(Imports $imports): string
    {
        return 'fake()->boolean()';
    }

    protected function fakerCall(): string
    {
        return 'boolean()';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\Checkbox';
    }

    public function getFilter(Imports $imports): ?string
    {
        $class = $imports->add('Javaabu\\Filters\\TernaryFilter');

        return "{$class}::make('{$this->getName()}')->label({$this->getLabelCall()})";
    }

    public function getBladeEntry(string $variable): string
    {
        return '<x-forms::boolean-entry name="' . $this->getName() . '" :label="' . $this->getLabelCall() . '" />';
    }

    public function getReactEntry(string $variable): string
    {
        return '<BooleanEntry name="' . $this->getName() . '" label={__("' . $this->escape($this->getLabel(), '"') . '")} />';
    }

    public function getReactComponents(): array
    {
        return ['TableCell', 'BooleanEntry'];
    }

    public function getTypeScriptType(): string
    {
        return 'boolean';
    }

    public function getValidValue(): string
    {
        return 'true';
    }

    public function getOtherValidValue(): string
    {
        return 'false';
    }

    public function getInvalidValue(): ?string
    {
        return null;
    }
}
