<?php

namespace Javaabu\Generators\FieldTypes;

class EnumField extends Field
{

    protected array $options;
    protected ?string $enum_class;

    /**
     * Constructor
     */
    public function __construct(
        string $name,
        array $options,
        ?string $enum_class = null,
        bool $nullable = false,
        $default = null,
        bool $unique = false
    )
    {
        parent::__construct(
            $name,
            $nullable,
            default: $default,
            unique: $unique
        );

        $this->options = $options;;
        $this->enum_class = $enum_class;
    }

    public function hasEnumClass(): bool
    {
        return ! empty($this->getEnumClass());
    }

    public function getEnumClassBasename(): string
    {
        return $this->hasEnumClass() ? class_basename($this->getEnumClass()) : '';
    }

    public function getEnumClass(): ?string
    {
        return $this->enum_class;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getEnumCases(): array
    {
        $enum = $this->getEnumClass();

        return $enum ? $enum::cases() : [];
    }

    public function getOptionsString(): string
    {
        return '[' . collect($this->getOptions())
            ->transform(function ($value) {
                return "'" . $value. "'";
            })->implode(', ') . ']';
    }

    public function generateFactoryStatement(): string
    {
        if ($this->hasEnumClass()) {
            $array = "array_column({$this->getEnumClass()}::cases(), 'value')";
        } else {
            $array = $this->getOptionsString();
        }

        return "randomElement($array)";
    }

    public function generateValidationRules(): array
    {
        if ($this->hasEnumClass()) {
            return ["Rule::in({$this->getEnumClassBasename()}::class)"];
        }

        return ['in:'.implode(',', $this->getOptions())];
    }

    public function generateFactoryInput(): string
    {
        $input = $this->getName();

        if ($this->hasEnumClass()) {
            $input .= ($this->isNullable() ? '?' : '') . '->value';
        }

        return $input;
    }

    public function shouldQuoteCast(): bool
    {
        return false;
    }

    public function generateCast(): ?string
    {
        return $this->hasEnumClass() ? $this->getEnumClassBasename() . '::class' : null;
    }

    public function generateFactoryDbValue(): string
    {
        $value = parent::generateFactoryDbValue();

        if ($this->hasEnumClass()) {
            $value .= '->value';
        }

        return $value;
    }

    public function generateWrongValue(): string
    {
        return '[]';
    }

    public function generateCorrectValue(): string
    {
        return $this->hasEnumClass() ? $this->getEnumClassBasename() . '::' . $this->getEnumCases()[0]->name . '->value' : "'" . $this->getOptions()[0] . "'";
    }

    public function generateDifferentCorrectValue(): string
    {
        return $this->hasEnumClass() ? $this->getEnumClassBasename() . '::' . $this->getEnumCases()[1]->name . '->value' : "'" . ($this->getOptions()[1] ?? $this->getOptions()[0]) . "'";
    }

    public function getFormComponentAttributes(): array
    {
        return [
            ':options' => $this->hasEnumClass() ? $this->generateEnumClassSelectOptions() : $this->getOptionsString(),
        ];
    }

    public function generateEnumClassSelectOptions(): string
    {
        if (method_exists($this->getEnumClass(), 'getLabels')) {
            return $this->getEnumClass() . '::getLabels()';
        }

        return  "array_column({$this->getEnumClass()}::cases(), 'name', 'value')";
    }

    public function getFormComponentName(): string
    {
        return 'select2';
    }
}
