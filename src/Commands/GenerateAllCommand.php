<?php

namespace Javaabu\Generators\Commands;

use Javaabu\Generators\Generators\BladeViewsGenerator;
use Javaabu\Generators\Generators\BulkActionsGenerator;
use Javaabu\Generators\Generators\ControllerGenerator;
use Javaabu\Generators\Generators\FactoryGenerator;
use Javaabu\Generators\Generators\FiltersGenerator;
use Javaabu\Generators\Generators\FormGenerator;
use Javaabu\Generators\Generators\InertiaPagesGenerator;
use Javaabu\Generators\Generators\ModelGenerator;
use Javaabu\Generators\Generators\PermissionsGenerator;
use Javaabu\Generators\Generators\PolicyGenerator;
use Javaabu\Generators\Generators\ResourceGenerator;
use Javaabu\Generators\Generators\RoutesGenerator;
use Javaabu\Generators\Generators\TestGenerator;
use Javaabu\Generators\Generators\TranslationsGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;
use Symfony\Component\Console\Input\InputOption;

class GenerateAllCommand extends GenerateCommand
{
    protected $name = 'generate:all';

    protected $description = 'Generate a complete admin resource from a table: model, factory, policy, permissions, filters, bulk actions, form, controller, routes, views and tests';

    /**
     * The parts, by the name --except takes.
     *
     * @var array<string, array<int, class-string>>
     */
    public const array PARTS = [
        'model' => [ModelGenerator::class],
        'factory' => [FactoryGenerator::class],
        'policy' => [PolicyGenerator::class],
        'permissions' => [PermissionsGenerator::class],
        'filters' => [FiltersGenerator::class],
        'bulk-actions' => [BulkActionsGenerator::class],
        'form' => [FormGenerator::class],
        'controller' => [ControllerGenerator::class],
        'routes' => [RoutesGenerator::class],
        'views' => [],
        'test' => [TestGenerator::class],
        'translations' => [TranslationsGenerator::class],
    ];

    protected function getOptions(): array
    {
        return [
            ...parent::getOptions(),
            ['except', null, InputOption::VALUE_REQUIRED, 'Parts to skip (comma separated): ' . implode(', ', array_keys(self::PARTS))],
        ];
    }

    protected function generators(ResourceDefinition $resource): array
    {
        $except = array_filter(array_map('trim', explode(',', (string) $this->option('except'))));

        $generators = [];

        foreach (self::PARTS as $part => $classes) {
            if (in_array($part, $except, true)) {
                continue;
            }

            if ($part === 'views') {
                $classes = $resource->isInertia()
                    ? [ResourceGenerator::class, InertiaPagesGenerator::class]
                    : [BladeViewsGenerator::class];
            }

            array_push($generators, ...$classes);
        }

        return $generators;
    }
}
