<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\ViewsGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

class GenerateViewsCommand extends BaseGenerateCommand
{

    protected $name = 'generate:views';

    protected $description = 'Generate model views based on your database table schema';

    protected string $generator_class = ViewsGenerator::class;

    protected function createOutput(string $table, array $columns): void
    {
        $generator = $this->getGenerator($table, $columns);
        $output = $generator->render();

        if (app()->runningInConsole()) {
            $this->info("Schema-based model views for table \"$table\" have been generated!");
            $this->info('Copy & paste these to your view files:');
        }

        $this->line($output);
    }

    protected function createFiles(string $table, array $columns, bool $force = false, string $path = ''): void
    {
        /** @var ViewsGenerator $generator */
        $generator = $this->getGenerator($table, $columns);

        $views = $generator->viewsToRender();

        foreach ($views as $method => $file_name) {
            $this->createView($file_name, $method, $generator, $force, $path);
        }

        $this->addSidebarLinks($generator);
    }

    protected function createView(string $file_name, string $method, ViewsGenerator $generator, bool $force = false, string $path = ''): void
    {
        // create the view
        $folder_name = StringCaser::pluralKebab($generator->getTable());
        $path = $this->getPath(resource_path('views/admin/' . $folder_name), $path);

        $file_path = $this->getFullFilePath($path, $file_name);

        $output = $generator->{$method}();

        if ($this->putContent($file_path, $output, $force)) {
            $this->info("$file_name created!");
        }
    }

    protected function addSidebarLinks(ViewsGenerator $generator): void
    {
        $file_path = app_path('Menus/AdminSidebar.php');

        $replacements = [
            [
                'search' => "->route('admin.home'),\n",
                'keep_search' => true,
                'content' => "\n" . $generator->renderSidebarLinks(),
            ],
        ];

        if ($this->appendContent($file_path, $replacements)) {
            $this->info("{$generator->getTable()} sidebar links added!");
        }
    }
}
