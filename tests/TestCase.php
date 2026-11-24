<?php

namespace Javaabu\Generators\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\GeneratorsServiceProvider;
use Javaabu\Generators\Tests\Fixtures\Enums\OrderStatuses;
use Javaabu\Generators\Tests\Fixtures\Enums\SingleCaseOrderStatuses;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            GeneratorsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $driver = env('DB_CONNECTION', 'sqlite');

        $connections = [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'generators'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'mariadb' => [
                'driver' => 'mariadb',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'generators'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '5432'),
                'database' => env('DB_DATABASE', 'generators'),
                'username' => env('DB_USERNAME', 'postgres'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8',
                'prefix' => '',
                'search_path' => 'public',
            ],
        ];

        $app['config']->set('database.default', $driver);
        $app['config']->set("database.connections.{$driver}", $connections[$driver]);

        // SQLite keeps no column comments, so name the enum classes in config
        $app['config']->set('generators.enums', [
            'orders.status' => OrderStatuses::class,
            'single_value_enum_orders.status' => SingleCaseOrderStatuses::class,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function migrateFreshUsing(): array
    {
        return [
            '--path' => __DIR__ . '/Fixtures/migrations',
            '--realpath' => true,
            '--drop-views' => true,
        ];
    }
}
