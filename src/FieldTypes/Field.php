<?php

namespace Javaabu\Generators\FieldTypes;

use Javaabu\Generators\Support\StringCaser;

abstract class Field
{

    protected string $name;
    protected bool $nullable;
    protected bool $unique;
    protected $min;
    protected $max;
    protected $default;

    /**
     * Constructor
     */
    public function __construct(
        string $name,
        bool $nullable = false,
        $default = null,
        $min = null,
        $max = null,
        bool $unique = false
    )
    {
        $this->name = $name;
        $this->nullable = $nullable;
        $this->min = $min;
        $this->max = $max;
        $this->default = $default;
        $this->unique = $unique;
    }

    public function isRequired(): bool
    {
        return (! $this->isNullable()) && (! $this->hasDefault());
    }

    public function hasDefault(): bool
    {
        return ! is_null($this->getDefault());
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMin()
    {
        return $this->min;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function getInputName(): string
    {
        return $this->getName();
    }

    public function getFormInputName(): string
    {
        return $this->getInputName();
    }

    public function getLabel(): string
    {
        return StringCaser::title($this->getInputName());
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

    public function getFormComponentAttributes(): array
    {
        return [];
    }

    public function getEntryComponentAttributes(): array
    {
        return [];
    }

    public function getTableCellComponentAttributes(): array
    {
        return [];
    }

    public function shouldRenderInputInline(): bool
    {
        return true;
    }

    public function shouldRenderEntryInline(): bool
    {
        return true;
    }

    protected function renderComponentAttributes(array $attributes): string
    {
        $attribs = [];

        foreach ($attributes as $attribute => $value) {
            if (is_bool($value)) {
                $attribs[] = $value ? $attribute : ':' . $attribute . '="false"';
            } else {
                $attribs[] = $attribute . '="' . $value . '"';
            }
        }

        return implode(' ', $attribs);
    }

    public function renderFormComponentAttributes(): string
    {
        $attributes = $this->getFormComponentAttributes();

        if ($this->isRequired()) {
            $attributes['required'] = true;
        }

        $attributes['inline'] = $this->shouldRenderInputInline();

        return $this->renderComponentAttributes($attributes);
    }

    public function renderFormComponent(): string
    {
        $attributes = $this->renderFormComponentAttributes();

        return '<x-forms::' . $this->getFormComponentName() . ' name="' . $this->getFormInputName() . '" ' . ($attributes ? $attributes . ' ' : '') . '/>';
    }

    public function renderEntryComponentAttributes(): string
    {
        $attributes = $this->getEntryComponentAttributes();

        $attributes['inline'] = $this->shouldRenderEntryInline();

        return $this->renderComponentAttributes($attributes);
    }

    public function getEntryAttributeName(): string
    {
        return $this->getInputName();
    }

    public function renderEntryComponent(): string
    {
        $attributes = $this->renderEntryComponentAttributes();

        return '<x-forms::' . $this->getEntryComponentName() . ' name="' . $this->getEntryAttributeName() . '" ' . ($attributes ? $attributes . ' ' : '') . '/>';
    }

    public function getEntryComponentName(): string
    {
        return 'text-entry';
    }

    public function renderTableCellComponentAttributes(): string
    {
        $attributes = $this->getTableCellComponentAttributes();

        return $this->renderComponentAttributes($attributes);
    }

    public function getTableCellAttributeName(): string
    {
        return $this->getEntryAttributeName();
    }

    public function renderTableCellComponent(): string
    {
        $attributes = $this->renderTableCellComponentAttributes();

        return '<x-forms::' . $this->getTableCellComponentName() . ' name="' . $this->getTableCellAttributeName() . '" ' . ($attributes ? $attributes . ' ' : '') . '/>';
    }

    public function renderTableTitleComponent(): string
    {
        $sortable = $this->isSortable() ? ' sortable="' . $this->getName() . '"' : '';

        return '<x-forms::table.heading :label="__(\'' . $this->getLabel() . '\')"' . $sortable . ' />';
    }

    public function getTableCellComponentName(): string
    {
        return 'table.cell';
    }

    public function generateFactoryDbValue(): string
    {
        return $this->getName();
    }

    public function formatFactoryDbValue(string $value): string
    {
        return $value;
    }

    public function generateWrongDbValue(): string
    {
        return $this->generateWrongValue();
    }

    public function generateCorrectDbValue(): string
    {
        return $this->generateCorrectValue();
    }

    public function generateDifferentCorrectDbValue(): string
    {
        return $this->generateDifferentCorrectValue();
    }

    public function shouldQuoteCast(): bool
    {
        return true;
    }

    public function generateFactoryInput(): string
    {
        return $this->getName();
    }

    public function renderAssignment(string $prefix = '$request->input(', string $suffix = ')'): string
    {
        return $this->getName() . ' = ' . $prefix . "'" . $this->getInputName() . "'" . $suffix;
    }

    public abstract function generateDifferentCorrectValue(): string;

    public abstract function getFormComponentName(): string;

    public abstract function generateFactoryStatement(): string;

    public abstract function generateValidationRules(): array;

    public abstract function generateCast(): ?string;

    public abstract function generateWrongValue(): string;

    public abstract function generateCorrectValue(): string;
}
