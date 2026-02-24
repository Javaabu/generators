<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateControllerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_controller_output(): void
    {
        $expected_content = $this->getTestStubContents('Controllers/CategoriesController.php');

        $this->artisan('generate:controller', ['table' => 'categories'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_controller_file(): void
    {
        $expected_path = $this->app->path('Http/Controllers/Admin/CategoriesController.php');
        $expected_content = $this->getTestStubContents('Controllers/CategoriesController.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:controller', ['table' => 'categories', '--create' => true])
             ->assertSuccessful();
    }
}
