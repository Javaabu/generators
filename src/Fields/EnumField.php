<?php

namespace Javaabu\Generators\Fields;

use BackedEnum;
use Illuminate\Support\Str;
use Javaabu\Generators\Support\Imports;

/**
 * A column with a fixed set of values: a database enum, a check constraint,
 * or a string column backed by an enum class.
 */
class EnumField extends Field
{
    /**
     * @param  array<int, string>  $options
     */
    public function __construct(
        string $name,
        protected array $options = [],
        protected ?string $enum_class = null,
        bool $nullable = false,
        mixed $default = null,
        bool $unique = false,
    ) {
        parent::__construct($name, $nullable, $default, $unique);
    }

    public function hasEnumClass(): bool
    {
        return $this->enum_class !== null;
    }

    public function getEnumClass(): ?string
    {
        return $this->enum_class ? ltrim($this->enum_class, '\\') : null;
    }

    /**
     * The values: the enum cases when the class exists, otherwise the options.
     *
     * @return array<int, string>
     */
    public function getValues(): array
    {
        $class = $this->getEnumClass();

        if ($class && enum_exists($class) && is_subclass_of($class, BackedEnum::class)) {
            return array_map(fn (BackedEnum $case) => (string) $case->value, $class::cases());
        }

        return $this->options;
    }

    /**
     * The value at a position, as PHP code.
     */
    protected function valueCode(int $index): string
    {
        $class = $this->getEnumClass();

        if ($class && enum_exists($class) && is_subclass_of($class, BackedEnum::class)) {
            $cases = $class::cases();
            $case = $cases[$index] ?? $cases[0];

            return class_basename($class) . "::{$case->name}->value";
        }

        $values = $this->getValues() ?: ['value'];

        return "'" . $this->escape($values[$index] ?? $values[0]) . "'";
    }

    public function isSortable(): bool
    {
        return true;
    }

    public function getCast(Imports $imports): ?string
    {
        if (! $this->hasEnumClass()) {
            return null;
        }

        return $imports->add($this->getEnumClass()) . '::class';
    }

    protected function fakerCall(): string
    {
        return '';
    }

    public function getFactoryValue(Imports $imports): string
    {
        $optional = $this->isNullable() ? 'optional()->' : '';

        if ($this->hasEnumClass()) {
            $class = $imports->add($this->getEnumClass());

            return "fake()->{$optional}randomElement({$class}::cases())";
        }

        return "fake()->{$optional}randomElement({$this->optionsArray()})";
    }

    protected function optionsArray(): string
    {
        return '[' . implode(', ', array_map(fn (string $option) => "'" . $this->escape($option) . "'", $this->getValues())) . ']';
    }

    /**
     * Options with labels, for forms and filters.
     */
    protected function optionsCode(Imports $imports): string
    {
        if ($this->hasEnumClass()) {
            return $imports->add($this->getEnumClass()) . '::class';
        }

        $options = array_map(
            fn (string $option) => "'" . $this->escape($option) . "' => __('" . $this->escape(Str::ucfirst(str_replace('_', ' ', $option))) . "')",
            $this->getValues()
        );

        return '[' . implode(', ', $options) . ']';
    }

    protected function stencilClass(): string
    {
        return 'Javaabu\\Stencil\\Fields\\Select';
    }

    protected function stencilModifiers(Imports $imports): array
    {
        return ["options({$this->optionsCode($imports)})"];
    }

    public function getFilter(Imports $imports): ?string
    {
        $class = $imports->add('Javaabu\\Filters\\SelectFilter');

        return "{$class}::make('{$this->getName()}')->label({$this->getLabelCall()})->options({$this->optionsCode($imports)})";
    }

    public function getResourceValue(): string
    {
        if (! $this->hasEnumClass()) {
            return parent::getResourceValue();
        }

        return '$this->' . $this->getName() . '?->value';
    }

    public function getTypeScriptType(): string
    {
        $values = $this->getValues();

        if (! $values) {
            return 'string';
        }

        return implode(' | ', array_map(fn (string $value) => '"' . str_replace('"', '\\"', $value) . '"', $values));
    }

    public function getValidValue(): string
    {
        return $this->valueCode(0);
    }

    public function getOtherValidValue(): string
    {
        return $this->valueCode(1);
    }

    public function getInvalidValue(): ?string
    {
        return "'ބަދަލު'";
    }

    public function getTestSetup(Imports $imports, string $prefix): array
    {
        if ($this->hasEnumClass()) {
            $imports->add($this->getEnumClass());
        }

        return [];
    }
}
