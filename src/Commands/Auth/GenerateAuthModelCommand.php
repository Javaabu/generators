<?php

namespace Javaabu\Generators\Commands\Auth;

use Javaabu\Generators\Generators\Auth\AuthModelGenerator;
use Javaabu\Generators\Support\StringCaser;

class GenerateAuthModelCommand extends BaseAuthGenerateCommand
{

    protected $name = 'generate:auth_model';

    protected $description = 'Generate auth model class based on your database table schema';

    protected string $generator_class = AuthModelGenerator::class;

    protected function createOutput(string $table, array $columns, string $auth_name): void
    {
        $generator = $this->getGenerator($table, $columns, $auth_name);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based auth model class for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your model class:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, string $auth_name, bool $force = false, string $path = ''): void
    {
        // create the model
        $this->createModel($table, $columns, $auth_name, $force, $path);

        $this->addSubjectTypeAndMorphMap($table);
    }

    protected function createModel(string $table, array $columns, string $auth_name, bool $force = false, string $path = ''): void
    {
        // create the model
        $path = $this->getPath(app_path('Models'), $path);

        $file_name = StringCaser::singularStudly($table) . '.php';
        $file_path = $this->getFullFilePath($path, $file_name);

        $generator = $this->getGenerator($table, $columns, $auth_name);
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
                'search' => "Relation::enforceMorphMap([\n",
                'keep_search' => true,
                'content' => $this->getRenderer()->addIndentation("'$morph_name' => \\App\\Models\\$class_name::class,\n", 3),
            ],
            [
                'search' => "SubjectTypes::register([\n",
                'keep_search' => true,
                'content' => $this->getRenderer()->addIndentation("\\App\\Models\\$class_name::class,\n", 3),
            ],
            [
                'search' => "CauserTypes::register([\n",
                'keep_search' => true,
                'force' => true,
                'content' => $this->getRenderer()->addIndentation("\\App\\Models\\$class_name::class,\n", 3),
            ],
        ];

        if ($this->appendContent($file_path, $replacements)) {
            $this->info("$table causer type, subject type and morph map added!");
        }
    }
}
