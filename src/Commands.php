<?php

namespace Javaabu\Generators;

use Javaabu\Generators\Commands\GenerateAllCommand;
use Javaabu\Generators\Commands\GenerateBulkActionsCommand;
use Javaabu\Generators\Commands\GenerateControllerCommand;
use Javaabu\Generators\Commands\GenerateExportCommand;
use Javaabu\Generators\Commands\GenerateFactoryCommand;
use Javaabu\Generators\Commands\GenerateFiltersCommand;
use Javaabu\Generators\Commands\GenerateFormCommand;
use Javaabu\Generators\Commands\GenerateModelCommand;
use Javaabu\Generators\Commands\GeneratePermissionsCommand;
use Javaabu\Generators\Commands\GeneratePolicyCommand;
use Javaabu\Generators\Commands\GenerateRollbackCommand;
use Javaabu\Generators\Commands\GenerateRoutesCommand;
use Javaabu\Generators\Commands\GenerateTestCommand;
use Javaabu\Generators\Commands\GenerateViewsCommand;

class Commands
{
    /**
     * @return array<int, class-string>
     */
    public static function all(): array
    {
        return [
            GenerateAllCommand::class,
            GenerateModelCommand::class,
            GenerateFactoryCommand::class,
            GeneratePolicyCommand::class,
            GeneratePermissionsCommand::class,
            GenerateFiltersCommand::class,
            GenerateBulkActionsCommand::class,
            GenerateFormCommand::class,
            GenerateControllerCommand::class,
            GenerateRoutesCommand::class,
            GenerateViewsCommand::class,
            GenerateTestCommand::class,
            GenerateExportCommand::class,
            GenerateRollbackCommand::class,
        ];
    }
}
