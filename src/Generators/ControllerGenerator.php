<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\BooleanField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\Generators\Concerns\GeneratesController;
use Javaabu\GeneratorHelpers\StringCaser;

class ControllerGenerator extends BaseGenerator
{
    use GeneratesController;

    /**
     * Render the controller
     */
    public function render(): string
    {
        return $this->renderController();
    }
}
