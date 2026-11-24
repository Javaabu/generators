<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ModelGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateModelCommand extends GenerateCommand
{
    protected $name = 'generate:model';

    protected $description = 'Generate the model of a table';

    protected function generators(ResourceDefinition $resource): array
    {
        return [ModelGenerator::class];
    }
}
