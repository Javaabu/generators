<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateAuthPolicyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_auth_policy_output(): void
    {
        $expected_content = $this->getTestStubContents('Policies/CustomerPolicy.php');

        $this->artisan('generate:auth_policy', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_auth_policy_file(): void
    {
        $expected_path = $this->app->path('Policies/CustomerPolicy.php');
        $expected_content = $this->getTestStubContents('Policies/CustomerPolicy.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:auth_policy', ['table' => 'customers', '--create' => true])
             ->assertSuccessful();
    }
}
