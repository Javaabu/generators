<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateRequestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_request_output(): void
    {
        $expected_content = $this->getTestStubContents('Requests/CategoriesRequest.php');

        $this->artisan('generate:request', ['table' => 'categories'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_request_file(): void
    {
        $expected_path = $this->app->path('Http/Requests/CategoriesRequest.php');
        $expected_content = $this->getTestStubContents('Requests/CategoriesRequest.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:request', ['table' => 'categories', '--create' => true])
             ->assertSuccessful();
    }
}
