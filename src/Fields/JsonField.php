<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

/**
 * A JSON column, edited as a list of tags.
 */
class JsonField extends Field
{
    public function isIndexColumn(): bool
    {
        return false;
    }

    public function getCast(Imports $imports): ?string
    {
        return "'array'";
    }

    protected function fakerCall(): string
    {
        return 'words(3)';
    }

    public function getFactoryValue(Imports $imports): string
    {
        return $this->isNullable() ? 'fake()->optional()->words(3)' : 'fake()->words(3)';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\Select';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        return ['multiple()', 'tags()'];
    }

    public function getBladeCell(string $variable): string
    {
        return '<x-forms::table.cell :label="' . $this->getLabelCall() . '">{{ implode(\', \', ' . $variable . '->' . $this->getName() . ' ?? []) }}</x-forms::table.cell>';
    }

    public function getBladeEntry(string $variable): string
    {
        return '<x-forms::text-entry :label="' . $this->getLabelCall() . '">{{ implode(\', \', ' . $variable . '->' . $this->getName() . ' ?? []) }}</x-forms::text-entry>';
    }

    public function getReactCell(string $variable): string
    {
        return '<TableCell label={__("' . $this->escape($this->getLabel(), '"') . '")} value={(' . $variable . '.' . $this->getName() . ' ?? []).join(", ")} />';
    }

    public function getReactEntry(string $variable): string
    {
        return '<TextEntry label={__("' . $this->escape($this->getLabel(), '"') . '")} value={(' . $variable . '.' . $this->getName() . ' ?? []).join(", ")} />';
    }

    public function getTypeScriptType(): string
    {
        return 'string[]';
    }

    public function getValidValue(): string
    {
        return "['ރަށް', 'atoll']";
    }

    public function getOtherValidValue(): string
    {
        return "['ފަޅު']";
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
        return "\$this->assertEquals({$input}, {$variable}->{$this->getName()});";
    }
}
