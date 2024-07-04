<?php

namespace Javaabu\Generators\FieldTypes;

use Illuminate\Support\Str;

class BooleanField extends Field
{
    /**
     * Constructor
     */
    public function __construct(
        string $name,
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
    }

    public function shouldRenderInputPlaceholder(): bool
    {
        return false;
    }

    public function generateFactoryStatement(): string
    {
        return 'boolean()';
    }

    public function generateValidationRules(): array
    {
        return ['boolean'];
    }

    public function generateCast(): ?string
    {
        return 'boolean';
    }

    public function generateWrongValue(): string
    {
        return "'foo'";
    }

    public function generateCorrectValue(): string
    {
        return 'true';
    }

    public function generateDifferentCorrectValue(): string
    {
        return 'false';
    }

    public function getFormComponentName(): string
    {
        return 'checkbox';
    }

    public function getFormComponentAttributes(): array
    {
        return [
            'value' => 1,
        ];
    }

    public function getEntryComponentName(): string
    {
        return 'boolean-entry';
    }

    public function renderAssignment(string $prefix = '$request->input(', string $suffix = ')'): string
    {
        if (Str::startsWith($suffix, ')')) {
            $suffix = ', false)';
        } elseif (Str::startsWith($suffix, ']')) {
            $suffix = '] ?? false';
        }

        return parent::renderAssignment($prefix, $suffix);
    }
}
