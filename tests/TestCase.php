<?php

namespace Javaabu\Generators\Tests;

use Javaabu\Generators\GeneratorsServiceProvider;
use Javaabu\Generators\Testing\InteractsWithTestFiles;
use Javaabu\Generators\Testing\InteractsWithTestStubs;
use Javaabu\Generators\Tests\TestSupport\Providers\TestServiceProvider;
use Javaabu\Schema\SchemaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithTestStubs;
    use InteractsWithTestFiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        $this->app['config']->set('database.default', 'mysql');

        $this->app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'database' => env('DB_DATABASE'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD', ''),
            'prefix'   => '',
        ]);

        $this->loadTestStubsFrom(__DIR__.'/stubs');

    }

    protected function getPackageProviders($app)
    {
        return [
            SchemaServiceProvider::class,
            GeneratorsServiceProvider::class,
            TestServiceProvider::class
        ];
    }
}
