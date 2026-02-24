<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class GenerateModelCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // delete all models
        $this->deleteFile($this->app->path('Models/Product.php'));
        $this->deleteFile($this->app->path('Models/Category.php'));

        // setup skeleton service provider
        $this->copyFile(
            $this->getTestStubPath('Providers/SkeletonAppServiceProvider.php'),
            $this->app->path('Providers/AppServiceProvider.php')
        );
    }

    protected function tearDown(): void
    {
        $this->deleteFile($this->app->path('Models/Product.php'));
        $this->deleteFile($this->app->path('Models/Category.php'));

        // setup standard service provider
        $this->copyFile(
            $this->getTestStubPath('Providers/AppServiceProvider.php'),
            $this->app->path('Providers/AppServiceProvider.php')
        );

        parent::tearDown();
    }

    public function test_it_can_generate_model_output(): void
    {
        $expected_content = $this->getTestStubContents('Models/Product.php');

        $this->artisan('generate:model', ['table' => 'products'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_a_new_model_file(): void
    {
        $expected_path = $this->app->path('Models/Product.php');
        $expected_content = $this->getTestStubContents('Models/Product.php');

        $this->artisan('generate:model', ['table' => 'products', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_adds_subject_types_and_morph_maps(): void
    {
        $expected_path = $this->app->path('Models/Category.php');
        $expected_content = $this->getTestStubContents('Models/Category.php');

        $this->artisan('generate:model', ['table' => 'categories', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);

        $expected_path = $this->app->path('Models/Product.php');
        $expected_content = $this->getTestStubContents('Models/Product.php');

        $this->artisan('generate:model', ['table' => 'products', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);

        $expected_path = $this->app->path('Providers/AppServiceProvider.php');
        $expected_content = $this->getTestStubContents('Providers/ModelsAppServiceProvider.php');
        $actual_content = $this->getGeneratedFileContents($expected_path);

        $this->assertEquals($expected_content, $actual_content);
    }
}
