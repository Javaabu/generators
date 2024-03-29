<?php

namespace Javaabu\Generators\FieldTypes;

class TimeField extends DateTypeField
{

    public function generateFactoryStatement(): string
    {
        return 'time()';
    }

    public function generateCast(): ?string
    {
        return 'datetime';
    }

    public function generateCorrectValue(): string
    {
        return "'14:54:00'";
    }

    public function generateDifferentCorrectValue(): string
    {
        return "'13:30:00'";
    }

    public function getFormComponentName(): string
    {
        return 'time';
    }
}
