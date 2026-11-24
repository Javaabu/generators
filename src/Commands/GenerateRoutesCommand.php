<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\RoutesGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateRoutesCommand extends GenerateCommand
{
    protected $name = 'generate:routes';

    protected $description = 'Add the admin routes of a table';

    protected function generators(ResourceDefinition $resource): array
    {
        return [RoutesGenerator::class];
    }
}
