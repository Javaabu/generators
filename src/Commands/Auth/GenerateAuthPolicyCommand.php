<?php

namespace Javaabu\Generators\Commands\Auth;

use Javaabu\Generators\Generators\Auth\AuthPolicyGenerator;
use Javaabu\Generators\Generators\Auth\BaseAuthGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateAuthPolicyCommand extends BaseAuthGenerateCommand
{

    protected $name = 'generate:auth_policy';

    protected $description = 'Generate auth model policy based on your database table schema';

    protected string $generator_class = AuthPolicyGenerator::class;

    protected function createOutput(string $table, array $columns, string $auth_name): void
    {
        $generator = $this->getGenerator($table, $columns, $auth_name);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based auth model policy for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your policy class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, string $auth_name, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(app_path('Policies'), $path);

        $file_name = StringCaser::singularStudly($table) . 'Policy.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns, $auth_name);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }
}
