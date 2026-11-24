<?php

namespace Javaabu\Generators\Fields;

use Javaabu\Generators\Support\Imports;

class TextField extends Field
{
    public function __construct(
        string $name,
        bool $nullable = false,
        mixed $default = null,
        bool $unique = false,
        protected ?int $max = null,
    ) {
        parent::__construct($name, $nullable, $default, $unique);
    }

    public function isSearchable(): bool
    {
        return true;
    }

    public function isIndexColumn(): bool
    {
        return false;
    }

    protected function fakerCall(): string
    {
        return 'paragraph()';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\Textarea';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        $modifiers = ['rows(4)'];

        if ($this->max && $this->max < 4294967295) {
            $modifiers[] = "maxLength({$this->max})";
        }

        $modifiers[] = 'columnSpanFull()';

        return $modifiers;
    }

    public function getBladeCell(string $variable): string
    {
        return '<x-forms::table.cell name="' . $this->getName() . '" :label="' . $this->getLabelCall() . '" multiline />';
    }

    public function getBladeEntry(string $variable): string
    {
        return '<x-forms::text-entry name="' . $this->getName() . '" :label="' . $this->getLabelCall() . '" multiline />';
    }

    public function getReactEntry(string $variable): string
    {
        return '<TextEntry name="' . $this->getName() . '" label={__("' . $this->escape($this->getLabel(), '"') . '")} multiline />';
    }

    public function getValidValue(): string
    {
        return "'ދިވެހިރާއްޖޭގެ ޖުމްހޫރިއްޔާ'";
    }

    public function getOtherValidValue(): string
    {
        return "'Republic of Maldives'";
    }
}
