<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\TestGenerator;
use Javaabu\Generators\Support\StringCaser;

class GenerateTestCommand extends BaseGenerateCommand
{

    protected $name = 'generate:test';

    protected $description = 'Generate model controller test based on your database table schema';

    protected function createOutput(string $table, array $columns): void
    {
        $generator = new TestGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model controller test for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your test class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(base_path('tests/Feature/Admin'), $path);

        $file_name = StringCaser::pluralStudly($table) . 'ControllerTest.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = new TestGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
