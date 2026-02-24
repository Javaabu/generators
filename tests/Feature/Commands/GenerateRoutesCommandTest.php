<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class GenerateRoutesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // setup skeleton route file
        $this->copyFile(
            $this->getTestStubPath('routes/skeletonAdmin.php'),
            $this->app->basePath('routes/admin.php')
        );
    }

    protected function tearDown(): void
    {
        $this->deleteFile($this->app->path('routes/admin.php'));

        parent::tearDown();
    }

    public function test_it_can_generate_routes_output(): void
    {
        $expected_content = $this->getTestStubContents('routes/_admin.stub');

        $this->artisan('generate:routes', ['table' => 'categories'])
             ->expectsOutput($expected_content);
    }


    public function test_it_can_append_routes_to_an_existing_routes_file(): void
    {
        $expected_path = $this->app->basePath('routes/admin.php');
        $expected_content = $this->getTestStubContents('routes/modelsAdmin.php');

        $this->artisan('generate:routes', ['table' => 'categories', '--create' => true])
            ->assertSuccessful();

        $this->artisan('generate:routes', ['table' => 'products', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }
}
