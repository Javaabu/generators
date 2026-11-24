<?php

namespace Javaabu\Generators\Fields;

use Illuminate\Support\Str;
use Javaabu\Generators\Support\Imports;

class DecimalField extends Field
{
    public function __construct(
        string $name,
        bool $nullable = false,
        mixed $default = null,
        bool $unique = false,
        protected int $precision = 10,
        protected int $scale = 2,
        protected bool $unsigned = false,
    ) {
        parent::__construct($name, $nullable, $default, $unique);
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function getMax(): string
    {
        $digits = max($this->precision - $this->scale, 1);

        // keep it within what a float holds exactly
        return Str::repeat('9', min($digits, 15));
    }

    public function getMin(): string
    {
        return $this->unsigned ? '0' : '-' . $this->getMax();
    }

    public function isSortable(): bool
    {
        return true;
    }

    public function getCast(Imports $imports): ?string
    {
        return "'decimal:{$this->scale}'";
    }

    protected function fakerCall(): string
    {
        $max = min((int) $this->getMax(), 10000);

        return "randomFloat({$this->scale}, 0, {$max})";
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\TextInput';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        return ['numeric()', "decimals({$this->scale})", "minValue({$this->getMin()})", "maxValue({$this->getMax()})"];
    }

    public function getFilter(Imports $imports): ?string
    {
        $class = $imports->add('Javaabu\\Filters\\NumberRangeFilter');

        return "{$class}::make('{$this->getName()}')->label({$this->getLabelCall()})";
    }

    public function getTypeScriptType(): string
    {
        return 'string';
    }

    public function getValidValue(): string
    {
        return $this->scale > 0 ? '10.5' : '10';
    }

    public function getOtherValidValue(): string
    {
        return $this->scale > 0 ? '20.25' : '20';
    }

    public function getInvalidValue(): ?string
    {
        return "'ބަދަލު'";
    }
}
