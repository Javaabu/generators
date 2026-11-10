<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\FactoryGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateFactoryCommand extends GenerateCommand
{
    protected $name = 'generate:factory';

    protected $description = 'Generate the model factory of a table';

    protected function generators(ResourceDefinition $resource): array
    {
        return [FactoryGenerator::class];
    }
}
