<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;

class GenerateApiControllerCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // delete all APIs
        $this->deleteDirectory($this->app->path('Http/Controllers/Api'));

        // setup skeleton route file
        $this->copyFile(
            $this->getTestStubPath('routes/skeletonApi.php'),
            $this->app->basePath('routes/api.php')
        );
    }

    protected function tearDown(): void
    {
        $this->deleteFile($this->app->path('routes/api.php'));

        $this->deleteDirectory($this->app->path('Http/Controllers/Api'));

        parent::tearDown();
    }

    public function test_it_can_generate_api_output(): void
    {
        $expected_content = $this->getTestStubContents('Controllers/Api/CategoriesController.php');

        $this->artisan('generate:api_controller', ['table' => 'categories'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_api_controller_file(): void
    {
        $expected_path = $this->app->path('Http/Controllers/Api/CategoriesController.php');
        $expected_content = $this->getTestStubContents('Controllers/Api/CategoriesController.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:api_controller', ['table' => 'categories', '--create' => true])
            ->assertSuccessful();
    }

    public function test_it_can_append_api_routes_to_an_existing_api_routes_file(): void
    {
        $expected_path = $this->app->basePath('routes/api.php');
        $expected_content = $this->getTestStubContents('routes/modelsApi.php');

        $this->artisan('generate:api_controller', ['table' => 'categories', '--create' => true])
            ->assertSuccessful();

        $this->artisan('generate:api_controller', ['table' => 'products', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }
}
