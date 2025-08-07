<?php

namespace Javaabu\Generators\FieldTypes;

class JsonField extends Field
{

    public function getFormInputName(): string
    {
        return $this->getInputName() . '[]';
    }

    public function generateFactoryStatement(): string
    {
        return 'words()';
    }

    public function formatFactoryDbValue(string $value): string
    {
        return "is_null($value) ? null : json_encode($value)";
    }

    public function generateValidationRules(): array
    {
        return ['array'];
    }

    public function generateCast(): ?string
    {
        return 'array';
    }

    public function generateWrongDbValue(): string
    {
        return 'json_encode(' . $this->generateWrongValue() . ')';
    }

    public function generateCorrectDbValue(): string
    {
        return 'json_encode(' . $this->generateCorrectValue() . ')';
    }

    public function generateDifferentCorrectDbValue(): string
    {
        return 'json_encode(' . $this->generateDifferentCorrectValue() . ')';
    }

    public function generateWrongValue(): string
    {
        return "'foo'";
    }

    public function generateCorrectValue(): string
    {
        return "['apple']";
    }

    public function generateDifferentCorrectValue(): string
    {
        return "['orange']";
    }

    public function getFormComponentName(): string
    {
        return 'select2';
    }

    public function getFormComponentAttributes(): array
    {
        return [
            ':options' => "['apple', 'orange']",
            'multiple' => true,
        ];
    }
}
