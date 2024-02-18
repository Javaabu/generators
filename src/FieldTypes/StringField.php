<?php

namespace Javaabu\Generators\FieldTypes;

class StringField extends Field
{
    public function generateFactoryStatement(): string
    {
        return 'passThrough(ucfirst($this->faker->text('.$this->getMax().')))';
    }

    public function generateValidationRules(): array
    {
        return ['string', 'max:'.$this->getMax()];
    }

    public function isSearchable(): bool
    {
        return true;
    }

    public function generateCast(): ?string
    {
        return 'string';
    }

    public function isSortable(): bool
    {
        return true;
    }

    public function generateWrongValue(): string
    {
        return '[]';
    }

    public function generateCorrectValue(): string
    {
        return "'foo'";
    }

    public function generateDifferentCorrectValue(): string
    {
        return "'bar'";
    }

    public function getComponentName(): string
    {
        return 'text';
    }

    public function getComponentAttributes(): array
    {
        return [
            'maxlength' => $this->getMax(),
        ];
    }
}
