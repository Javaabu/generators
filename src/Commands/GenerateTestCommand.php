<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\TestGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateTestCommand extends GenerateCommand
{
    protected $name = 'generate:test';

    protected $description = 'Generate the controller tests of a table';

    protected function generators(ResourceDefinition $resource): array
    {
        return [TestGenerator::class];
    }
}
