<?php

namespace Tests;

use App\Providers\AppServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * The integration app's base test case: a Laravel app in build/app (built by
 * workbench/build.php) with every Javaabu next-gen package discovered. The
 * tests javaabu/generators writes extend it, like they extend an app's own
 * Tests\TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    public static function applicationBasePath(): string
    {
        return dirname(__DIR__);
    }

    public function ignorePackageDiscoveriesFrom(): array
    {
        return [];
    }

    protected function getPackageProviders($app): array
    {
        return [AppServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        tap($app['config'], function (Repository $config) {
            $config->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');
            $config->set('app.locale', 'en');

            $config->set('database.default', 'testing');
            $config->set('database.connections.testing', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);

            $config->set('session.driver', 'array');
            $config->set('cache.default', 'array');
            $config->set('queue.default', 'sync');

            $config->set('auth.providers.users.model', 'App\\Models\\User');

            $config->set('permission.cache.store', 'array');

            $config->set('inertia.testing.ensure_pages_exist', true);
            $config->set('inertia.testing.page_paths', [resource_path('js/pages')]);
            $config->set('inertia.testing.page_extensions', ['tsx']);

            $config->set('media-library.media_model', 'Javaabu\\Mediapicker\\Models\\Media');
            $config->set('media-library.disk_name', 'public');

            $config->set('generators.enums', [
                'products.type' => 'App\\Enums\\ProductTypes',
            ]);
        });
    }

    protected function defineRoutes($router): void
    {
        // routes/web.php loads the admin routes the generators write
        /** @var Router $router */
        $router->group([], base_path('routes/web.php'));
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(database_path('migrations'));
    }
}
