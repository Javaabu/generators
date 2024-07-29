<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\FactoryGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateFactoryCommand extends BaseGenerateCommand
{

    protected $name = 'generate:factory';

    protected $description = 'Generate model factory based on your database table schema';

    protected string $generator_class = FactoryGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based factory for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your factory class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(database_path('factories'), $path);

        $file_name = StringCaser::singularStudly($table) . 'Factory.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
