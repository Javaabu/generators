<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ControllerGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateControllerCommand extends BaseGenerateCommand
{

    protected $name = 'generate:controller';

    protected $description = 'Generate model controller based on your database table schema';

    protected string $generator_class = ControllerGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model controller for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your controller class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(app_path('Http/Controllers/Admin'), $path);

        $file_name = StringCaser::pluralStudly($table) . 'Controller.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
