<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\FormGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateFormCommand extends GenerateCommand
{
    protected $name = 'generate:form';

    protected $description = 'Generate the form of a table (javaabu/stencil)';

    protected function generators(ResourceDefinition $resource): array
    {
        return [FormGenerator::class];
    }
}
