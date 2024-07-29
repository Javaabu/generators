<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\PolicyGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GeneratePolicyCommand extends BaseGenerateCommand
{

    protected $name = 'generate:policy';

    protected $description = 'Generate model policy based on your database table schema';

    protected string $generator_class = PolicyGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model policy for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your policy class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(app_path('Policies'), $path);

        $file_name = StringCaser::singularStudly($table) . 'Policy.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
