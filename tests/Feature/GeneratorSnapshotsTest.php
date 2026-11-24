<?php

namespace Javaabu\Generators\Tests\Feature;

use Javaabu\Generators\Generators\BladeViewsGenerator;
use Javaabu\Generators\Generators\BulkActionsGenerator;
use Javaabu\Generators\Generators\ControllerGenerator;
use Javaabu\Generators\Generators\ExportGenerator;
use Javaabu\Generators\Generators\FactoryGenerator;
use Javaabu\Generators\Generators\FiltersGenerator;
use Javaabu\Generators\Generators\FormGenerator;
use Javaabu\Generators\Generators\Generator;
use Javaabu\Generators\Generators\InertiaPagesGenerator;
use Javaabu\Generators\Generators\ModelGenerator;
use Javaabu\Generators\Generators\PermissionsGenerator;
use Javaabu\Generators\Generators\PolicyGenerator;
use Javaabu\Generators\Generators\ResourceGenerator;
use Javaabu\Generators\Generators\RoutesGenerator;
use Javaabu\Generators\Generators\TestGenerator;
use Javaabu\Generators\Resource\ResourceOptions;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Tests\Concerns\AssertsSnapshots;
use Javaabu\Generators\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * The generated code, compared with reviewed snapshots in tests/snapshots.
 * Whether the code works is checked by the integration app (workbench/).
 */
class GeneratorSnapshotsTest extends TestCase
{
    use AssertsSnapshots;

    /**
     * @return array<string, array{0: string, 1: array<string, mixed>, 2: array<int, class-string<Generator>>}>
     */
    public static function resources(): array
    {
        $all = [
            ModelGenerator::class,
            FactoryGenerator::class,
            PolicyGenerator::class,
            PermissionsGenerator::class,
            FiltersGenerator::class,
            BulkActionsGenerator::class,
            FormGenerator::class,
            ControllerGenerator::class,
            RoutesGenerator::class,
            TestGenerator::class,
        ];

        return [
            'products, blade' => ['products', [], [...$all, BladeViewsGenerator::class, ExportGenerator::class]],
            'products, inertia' => ['products', ['views' => 'inertia'], [ControllerGenerator::class, ResourceGenerator::class, InertiaPagesGenerator::class, TestGenerator::class]],
            'categories, blade' => ['categories', [], [ModelGenerator::class, FiltersGenerator::class, BladeViewsGenerator::class]],
            'orders, enum class and a key to a slug' => ['orders', [], [ModelGenerator::class, FactoryGenerator::class, FormGenerator::class, TestGenerator::class]],
            'products nested in categories, blade' => ['products', ['parent' => 'categories', 'index' => 'name,price', 'form' => 'name,price,status,tags'], [ControllerGenerator::class, BulkActionsGenerator::class, RoutesGenerator::class, BladeViewsGenerator::class, TestGenerator::class]],
            'products nested in categories, inertia' => ['products', ['parent' => 'category_id', 'views' => 'inertia', 'index' => 'name,price', 'form' => 'name,price'], [ControllerGenerator::class, ResourceGenerator::class, InertiaPagesGenerator::class, TestGenerator::class]],
            'posts with media, blade' => ['posts', ['media' => 'featured_image:image,gallery:image:multiple,attachments:document:multiple'], [ModelGenerator::class, FormGenerator::class, BladeViewsGenerator::class]],
            'posts with media, inertia' => ['posts', ['media' => 'featured_image,attachments:document:multiple', 'views' => 'inertia'], [ResourceGenerator::class, InertiaPagesGenerator::class]],
            'tags, a pivot on the other side' => ['tags', [], [ModelGenerator::class, FormGenerator::class]],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<int, class-string<Generator>>  $generators
     */
    #[Test]
    #[DataProvider('resources')]
    public function it_generates_the_code_in_the_snapshots(string $table, array $options, array $generators): void
    {
        if (config('database.default') !== 'sqlite') {
            $this->markTestSkipped('The snapshots are of the SQLite schema; other drivers keep string lengths and comments.');
        }

        $resource = app(ResourceResolver::class)->resolve($table, ResourceOptions::fromArray($options));

        $output = [];

        foreach ($generators as $class) {
            foreach ((new $class($resource))->files() as $file) {
                $output[] = "// {$file->relativePath()}\n{$file->contents}";
            }
        }

        $name = str_replace([', ', ' '], ['.', '-'], $this->dataName());

        $this->assertMatchesSnapshot("{$name}.txt", implode("\n", $output));
    }
}
