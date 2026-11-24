<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\PermissionsGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GeneratePermissionsCommand extends GenerateCommand
{
    protected $name = 'generate:permissions';

    protected $description = 'Register the permissions of a table (javaabu/permissions)';

    protected function generators(ResourceDefinition $resource): array
    {
        return [PermissionsGenerator::class];
    }
}
