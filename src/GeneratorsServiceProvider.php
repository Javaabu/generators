<?php

namespace Javaabu\Generators;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Javaabu\GeneratorHelpers\StubRenderer;
use Javaabu\Generators\Schema\RelationResolver;
use Javaabu\Generators\Schema\SchemaInspector;

class GeneratorsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/generators.php', 'generators');

        $this->app->singleton(SchemaInspector::class, fn ($app) => new SchemaInspector($app->make(DatabaseManager::class), config('generators.connection')));
        $this->app->singleton(RelationResolver::class);
    }

    public function boot(): void
    {
        StubRenderer::loadStubsFrom(__DIR__ . '/../stubs', 'generators');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'generators');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/generators.php' => config_path('generators.php'),
        ], 'generators-config');

        $this->publishes([
            __DIR__ . '/../stubs' => base_path('stubs/vendor/generators'),
        ], 'generators-stubs');

        $this->commands(Commands::all());
    }
}
