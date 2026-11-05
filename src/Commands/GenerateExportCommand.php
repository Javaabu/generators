<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ExportGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateExportCommand extends GenerateCommand
{
    protected $name = 'generate:export';

    protected $description = 'Generate the export of a table (javaabu/exports)';

    protected function generators(ResourceDefinition $resource): array
    {
        return [ExportGenerator::class];
    }
}
