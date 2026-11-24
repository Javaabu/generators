<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\BladeViewsGenerator;
use Javaabu\Generators\Generators\InertiaPagesGenerator;
use Javaabu\Generators\Generators\ResourceGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateViewsCommand extends GenerateCommand
{
    protected $name = 'generate:views';

    protected $description = 'Generate the Blade views or Inertia React pages of a table';

    protected function generators(ResourceDefinition $resource): array
    {
        if ($resource->isInertia()) {
            return [ResourceGenerator::class, InertiaPagesGenerator::class];
        }

        return [BladeViewsGenerator::class];
    }
}
