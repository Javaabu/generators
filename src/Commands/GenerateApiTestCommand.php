<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ApiTestGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateApiTestCommand extends BaseGenerateCommand
{

    protected $name = 'generate:api_test';

    protected $description = 'Generate model api controller test based on your database table schema';

    protected string $generator_class = ApiTestGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model api controller test for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your api controller test file:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(base_path('tests/Feature/Controllers/Api'), $path);

        $file_name = StringCaser::pluralStudly($table) . 'ControllerTest.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
