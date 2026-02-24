<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GeneratePolicyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_policy_output(): void
    {
        $expected_content = $this->getTestStubContents('Policies/CategoryPolicy.php');

        $this->artisan('generate:policy', ['table' => 'categories'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_policy_file(): void
    {
        $expected_path = $this->app->path('Policies/CategoryPolicy.php');
        $expected_content = $this->getTestStubContents('Policies/CategoryPolicy.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:policy', ['table' => 'categories', '--create' => true])
             ->assertSuccessful();
    }
}
