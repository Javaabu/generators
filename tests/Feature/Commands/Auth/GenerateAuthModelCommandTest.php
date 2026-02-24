<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class GenerateAuthModelCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // delete all models
        $this->deleteFile($this->app->path('Models/Customer.php'));
        $this->deleteFile($this->app->path('Models/PublicUser.php'));

        // setup skeleton service provider
        $this->copyFile(
            $this->getTestStubPath('Providers/SkeletonAppServiceProvider.php'),
            $this->app->path('Providers/AppServiceProvider.php')
        );
    }

    protected function tearDown(): void
    {
        $this->deleteFile($this->app->path('Models/Customer.php'));
        $this->deleteFile($this->app->path('Models/PublicUser.php'));

        // setup standard service provider
        $this->copyFile(
            $this->getTestStubPath('Providers/AppServiceProvider.php'),
            $this->app->path('Providers/AppServiceProvider.php')
        );

        parent::tearDown();
    }

    public function test_it_can_generate_auth_model_output(): void
    {
        $expected_content = $this->getTestStubContents('Models/Customer.php');

        $this->artisan('generate:auth_model', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_a_new_auth_model_file(): void
    {
        $expected_path = $this->app->path('Models/Customer.php');
        $expected_content = $this->getTestStubContents('Models/Customer.php');

        $this->artisan('generate:auth_model', ['table' => 'customers', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_adds_auth_causer_types_and_subject_types_and_morph_maps(): void
    {
        $expected_path = $this->app->path('Models/PublicUser.php');
        $expected_content = $this->getTestStubContents('Models/PublicUser.php');

        $this->artisan('generate:auth_model', ['table' => 'public_users', '--create' => true, '--auth_name' => 'portal'])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);

        $this->assertEquals($expected_content, $actual_content);

        $expected_path = $this->app->path('Models/Customer.php');
        $expected_content = $this->getTestStubContents('Models/Customer.php');

        $this->artisan('generate:auth_model', ['table' => 'customers', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);

        $expected_path = $this->app->path('Providers/AppServiceProvider.php');
        $expected_content = $this->getTestStubContents('Providers/AuthModelsAppServiceProvider.php');
        $actual_content = $this->getGeneratedFileContents($expected_path);

        $this->assertEquals($expected_content, $actual_content);
    }
}
