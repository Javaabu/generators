<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateApiTestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_test_output(): void
    {
        $expected_content = $this->getTestStubContents('tests/Api/CategoriesControllerTest.stub');

        $this->artisan('generate:api_test', ['table' => 'categories'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_test_file(): void
    {
        $expected_path = $this->app->basePath('tests/Feature/Controllers/Api/CategoriesControllerTest.php');
        $expected_content = $this->getTestStubContents('tests/Api/CategoriesControllerTest.stub');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:api_test', ['table' => 'categories', '--create' => true])
             ->assertSuccessful();
    }
}
