<?php

namespace Javaabu\Generators\Commands\Auth;

use Javaabu\Generators\Generators\Auth\AuthFactoryGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateAuthFactoryCommand extends BaseAuthGenerateCommand
{

    protected $name = 'generate:auth_factory';

    protected $description = 'Generate auth model factory based on your database table schema';

    protected string $generator_class = AuthFactoryGenerator::class;

    protected function createOutput(string $table, array $columns, string $auth_name): void
    {
        $generator = $this->getGenerator($table, $columns, $auth_name);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based factory for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your factory class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, string $auth_name, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(database_path('factories'), $path);

        $file_name = StringCaser::singularStudly($table) . 'Factory.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns, $auth_name);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
