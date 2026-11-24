<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\BulkActionsGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateBulkActionsCommand extends GenerateCommand
{
    protected $name = 'generate:bulk-actions';

    protected $description = 'Generate the bulk actions of a table (javaabu/bulk-actions)';

    protected function generators(ResourceDefinition $resource): array
    {
        return [BulkActionsGenerator::class];
    }
}
