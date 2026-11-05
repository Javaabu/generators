<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ControllerGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateControllerCommand extends GenerateCommand
{
    protected $name = 'generate:controller';

    protected $description = 'Generate the admin controller of a table';

    protected function generators(ResourceDefinition $resource): array
    {
        return [ControllerGenerator::class];
    }
}
