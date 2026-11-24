<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\FiltersGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateFiltersCommand extends GenerateCommand
{
    protected $name = 'generate:filters';

    protected $description = 'Generate the index filters and sorts of a table (javaabu/filters)';

    protected function generators(ResourceDefinition $resource): array
    {
        return [FiltersGenerator::class];
    }
}
