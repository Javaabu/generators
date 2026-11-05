<?php

namespace Javaabu\Generators\Fields;

use Illuminate\Support\Str;
use Javaabu\Generators\Support\Imports;

/**
 * A column of the resource, and everything the generators write for it:
 * cast, factory value, stencil field, filter, table cell, show entry,
 * TypeScript type and test values.
 */
abstract class Field
{
    public function __construct(
        protected string $name,
        protected bool $nullable = false,
        protected mixed $default = null,
        protected bool $unique = false,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Naming
    |--------------------------------------------------------------------------
    */

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The name of the form state and input.
     */
    public function getInputName(): string
    {
        return $this->getName();
    }

    /**
     * The attribute to display (a relation for foreign keys).
     */
    public function getDisplayName(): string
    {
        return $this->getName();
    }

    public function getLabel(): string
    {
        return Str::ucfirst(str_replace('_', ' ', Str::snake($this->getDisplayName())));
    }

    public function getLabelCall(): string
    {
        return "__('" . $this->escape($this->getLabel()) . "')";
    }

    /*
    |--------------------------------------------------------------------------
    | Column
    |--------------------------------------------------------------------------
    */

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    public function isRequired(): bool
    {
        return (! $this->isNullable()) && (! $this->hasDefault());
    }

    public function isFillable(): bool
    {
        return true;
    }

    public function isSearchable(): bool
    {
        return false;
    }

    public function isSortable(): bool
    {
        return false;
    }

    /**
     * Whether it can be shown in a form by default.
     */
    public function isFormField(): bool
    {
        return true;
    }

    /**
     * Whether it is a good default column for the index table.
     */
    public function isIndexColumn(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    */

    /**
     * The cast, as PHP code (a quoted string or a class constant), or null.
     */
    public function getCast(Imports $imports): ?string
    {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */

    /**
     * The faker call after fake()->, e.g. text(255).
     */
    abstract protected function fakerCall(): string;

    /**
     * The factory value expression.
     */
    public function getFactoryValue(Imports $imports): string
    {
        $call = $this->fakerCall();

        $unique = $this->isUnique() ? 'unique()->' : '';

        $optional = $this->isNullable() && ! $this->isUnique() ? 'optional()->' : '';

        return "fake()->{$unique}{$optional}{$call}";
    }

    /*
    |--------------------------------------------------------------------------
    | Form (javaabu/stencil)
    |--------------------------------------------------------------------------
    */

    /**
     * The stencil field class.
     */
    abstract protected function stencilClass(): string;

    /**
     * Calls after make() and label(), e.g. ['maxLength(255)'].
     *
     * @return array<int, string>
     */
    protected function stencilModifiers(Imports $imports): array
    {
        return [];
    }

    /**
     * The stencil field definition, without a trailing comma.
     *
     * @param  array<int, string>  $extra  extra calls, e.g. translatable()
     */
    public function getStencilField(Imports $imports, array $extra = []): string
    {
        $class = $imports->add($this->stencilClass());

        $calls = ["{$class}::make('{$this->getInputName()}')", "label({$this->getLabelCall()})"];

        if ($this->isRequired() && $this->canBeRequired()) {
            $calls[] = 'required()';
        }

        $calls = [...$calls, ...$this->stencilModifiers($imports), ...$extra];

        if (count($calls) <= 3 && strlen(implode('->', $calls)) <= 100) {
            return implode('->', $calls);
        }

        $first = array_shift($calls);

        return $first . "\n" . implode("\n", array_map(fn (string $call) => "    ->{$call}", $calls));
    }

    protected function canBeRequired(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Filters (javaabu/filters)
    |--------------------------------------------------------------------------
    */

    /**
     * A filter definition for this field, or null for none.
     */
    public function getFilter(Imports $imports): ?string
    {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function getBladeCell(string $variable): string
    {
        return '<x-forms::table.cell name="' . $this->getName() . '" :label="' . $this->getLabelCall() . '" />';
    }

    public function getBladeEntry(string $variable): string
    {
        return '<x-forms::text-entry name="' . $this->getName() . '" :label="' . $this->getLabelCall() . '" />';
    }

    public function getReactCell(string $variable): string
    {
        return '<TableCell name="' . $this->getName() . '" label={__("' . $this->escape($this->getLabel(), '"') . '")} />';
    }

    public function getReactEntry(string $variable): string
    {
        return '<TextEntry name="' . $this->getName() . '" label={__("' . $this->escape($this->getLabel(), '"') . '")} />';
    }

    /**
     * React components from @javaabu/forms the cell and entry use.
     *
     * @return array<int, string>
     */
    public function getReactComponents(): array
    {
        return ['TableCell', 'TextEntry'];
    }

    /**
     * The TypeScript type of the attribute in the resource JSON.
     */
    public function getTypeScriptType(): string
    {
        return 'string';
    }

    /**
     * The value in the JSON resource, as PHP code.
     */
    public function getResourceValue(): string
    {
        return '$this->' . $this->getName();
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    /**
     * A valid input value, as PHP code.
     */
    abstract public function getValidValue(): string;

    /**
     * Another valid input value, for updates.
     */
    abstract public function getOtherValidValue(): string;

    /**
     * An input value the form rejects, or null when every value is accepted.
     */
    public function getInvalidValue(): ?string
    {
        return '[]';
    }

    /**
     * The value expected in the database for an input value, or null when
     * it can't be compared in assertDatabaseHas() on every driver.
     */
    public function getDatabaseValue(string $input): ?string
    {
        return $input;
    }

    /**
     * Code that asserts the saved value, when it isn't compared in the database.
     */
    public function getSavedValueAssertion(string $variable, string $input): ?string
    {
        return null;
    }

    /**
     * Setup code a test needs for the valid values, e.g. a related record.
     *
     * @return array<int, string>
     */
    public function getTestSetup(Imports $imports, string $prefix): array
    {
        return [];
    }

    protected function escape(string $value, string $quote = "'"): string
    {
        return str_replace([$quote], ['\\' . $quote], $value);
    }
}
