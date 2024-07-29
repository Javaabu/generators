<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ApiControllerGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateApiControllerCommand extends BaseGenerateCommand
{

    protected $name = 'generate:api_controller';

    protected $description = 'Generate model api controller based on your database table schema';

    protected string $generator_class = ApiControllerGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model api controller for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your api controller file:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        $path = $this->getPath(app_path('Http/Controllers/Api'), $path);

        $file_name = StringCaser::pluralStudly($table) . 'Controller.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }

        $this->generateApiRoutes($generator);
    }

    protected function generateApiRoutes(ApiControllerGenerator $generator): void
    {
        $path = base_path('routes');

        $table = $generator->getTable();
        $file_name = 'api.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $output = $generator->renderRoutes();

        $replacements = [
            [
                'search' => '// Api Public Routes - DONOT REMOVE' . "\n",
                'keep_search' => true,
                'content' => "\n" . $output,
            ],
            [
                'search' => 'use Illuminate\\Support\\Facades\\Route;'."\n",
                'keep_search' => true,
                'content' => 'use App\\Http\\Controllers\\Api\\' . StringCaser::pluralStudly($generator->getTable()) . 'Controller;' . "\n",
            ],
        ];

        if ($this->appendContent($file_path, $replacements)) {
            $this->info("$table api routes created!");
        }
    }
}
