<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\RequestGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateRequestCommand extends BaseGenerateCommand
{

    protected $name = 'generate:request';

    protected $description = 'Generate form request based on your database table schema';

    protected string $generator_class = RequestGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based form request for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your request class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(app_path('Http/Requests'), $path);

        $file_name = StringCaser::pluralStudly($table) . 'Request.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
