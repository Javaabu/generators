<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\RoutesGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateRoutesCommand extends BaseGenerateCommand
{

    protected $name = 'generate:routes';

    protected $description = 'Generate model routes based on your database table schema';

    protected string $generator_class = RoutesGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model routes for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your routes file:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(base_path('routes'), $path);

        $file_name = 'admin.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        $replacements = [
            [
                'search' => '// Generator Routes - DONOT REMOVE'."\n\n",
                'keep_search' => true,
                'content' => $output . "\n",
            ],
            [
                'search' => 'use Illuminate\\Support\\Facades\\Route;'."\n",
                'keep_search' => true,
                'content' => 'use App\\Http\\Controllers\\Admin\\' . StringCaser::pluralStudly($generator->getTable()) . 'Controller;' . "\n",
            ],
        ];

        if ($this->appendContent($file_path, $replacements)) {
            $this->info("$table routes created!");
        }
    }
}
