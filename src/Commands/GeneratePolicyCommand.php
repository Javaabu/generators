<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\PolicyGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GeneratePolicyCommand extends GenerateCommand
{
    protected $name = 'generate:policy';

    protected $description = 'Generate the policy of a table (javaabu/permissions)';

    protected function generators(ResourceDefinition $resource): array
    {
        return [PolicyGenerator::class];
    }
}
