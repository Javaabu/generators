<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\PermissionsGenerator;
use Javaabu\Generators\Support\StringCaser;

class GeneratePermissionsCommand extends BaseGenerateCommand
{

    protected $name = 'generate:permissions';

    protected $description = 'Generate model permissions based on your database table schema';

    protected function createOutput(string $table, array $columns): void
    {
        $generator = new PermissionsGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model permissions for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your permissions class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(database_path('seeders'), $path);

        $stub = 'seeders/PermissionsSeeder.stub';
        $file_name = 'PermissionsSeeder.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = new PermissionsGenerator($table, $columns);
        $output = $generator->render();

        $replacements = [
            [
                'search' => 'protected $data = ['."\n",
                'keep_search' => true,
                'content' => $output . "\n",
            ],
        ];

        if ($this->appendContent($file_path, $replacements, $stub)) {
            $this->info("$table permissions created!");
        }
    }
}
