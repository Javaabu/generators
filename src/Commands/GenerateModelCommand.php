<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ModelGenerator;
use Javaabu\Generators\Support\StringCaser;

class GenerateModelCommand extends BaseGenerateCommand
{

    protected $name = 'generate:model';

    protected $description = 'Generate model class based on your database table schema';

    protected function createOutput(string $table, array $columns): void
    {
        $generator = new ModelGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model class for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your model class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        // create the model
        $this->createModel($table, $columns, $force, $path);

        $this->addSubjectTypeAndMorphMap($table);
    }

    protected function createModel(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        // create the model
        $path = $this->getPath(app_path('Models'), $path);

        $file_name = StringCaser::singularStudly($table) . '.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = new ModelGenerator($table, $columns);
        $output = $generator->render();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }

    protected function addSubjectTypeAndMorphMap(string $table): void
    {
        $file_path = app_path('Providers/AppServiceProvider.php');

        $morph_name = StringCaser::singularSnake($table);
        $class_name = StringCaser::singularStudly($table);

        $replacements = [
            [
                'search' => "Relation::morphMap([\n",
                'keep_search' => true,
                'content' => $this->renderer->addIndentation("'$morph_name' => \\App\\Models\\$class_name::class,\n", 3),
            ],
            [
                'search' => "SubjectTypes::register([\n",
                'keep_search' => true,
                'content' => $this->renderer->addIndentation("\\App\\Models\\$class_name::class,\n", 3),
            ],
        ];

        if ($this->appendContent($file_path, $replacements)) {
            $this->info("$table subject type and morph map added!");
        }
    }
}
