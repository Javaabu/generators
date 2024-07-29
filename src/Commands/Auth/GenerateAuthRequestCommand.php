<?php

namespace Javaabu\Generators\Commands\Auth;

use Javaabu\Generators\Generators\Auth\AuthRequestGenerator;
use Javaabu\Generators\Generators\Auth\BaseAuthGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateAuthRequestCommand extends BaseAuthGenerateCommand
{

    protected $name = 'generate:auth_request';

    protected $description = 'Generate form request based on your database table schema';

    protected string $generator_class = AuthRequestGenerator::class;

    protected function createOutput(string $table, array $columns, string $auth_name): void
    {
        $generator = $this->getGenerator($table, $columns, $auth_name);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based auth form request for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your request class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, string $auth_name, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(app_path('Http/Requests'), $path);

        $file_name = StringCaser::pluralStudly($table) . 'Request.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns, $auth_name);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
