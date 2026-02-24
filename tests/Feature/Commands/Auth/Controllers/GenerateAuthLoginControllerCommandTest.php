<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateAuthLoginControllerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_auth_login_controller_output(): void
    {
        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/LoginController.php');

        $this->artisan('generate:auth_login_controller', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_auth_login_controller_file(): void
    {
        $expected_path = $this->app->path('Http/Controllers/Customer/Auth/LoginController.php');
        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/LoginController.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:auth_login_controller', ['table' => 'customers', '--create' => true])
             ->assertSuccessful();
    }
}
