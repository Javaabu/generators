<?php

namespace Javaabu\Generators\Commands;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

abstract class MultipleGeneratorCommand extends BaseGenerateCommand
{
    /** @return array */
    protected function getOptions()
    {
        $options = parent::getOptions();

        $options[] = ['except', null, InputOption::VALUE_REQUIRED, 'Which commands to skip', ''];

        return $options;
    }

    protected function getCommands(): array
    {
        $defined_commands = property_exists($this, 'commands') ? $this->commands : [];

        // Options
        $skipped_commands = (array) array_filter(explode(',', $this->option('except')));

        return array_diff($defined_commands, $skipped_commands);
    }

    protected function createOutput(string $table, array $columns): void
    {
        $this->callCommands($table, $columns, false);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $this->callCommands($table, $columns, true, $force, $path);
    }

    protected function callCommands(string $table, array $columns, bool $create = false, bool $force = false, string $path = ''): void
    {
        $commands = $this->getCommands();

        foreach ($commands as $command) {
            Artisan::call("generate:$command", [
                'table' => $table,
                '--columns' => implode(',', $columns),
                '--create' => $create,
                '--force' => $force,
                '--path' => $path,
            ]);

            $this->info(Artisan::output());
        }
    }
}
