<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateAuthForgotPasswordControllerCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_auth_forgot_password_controller_output(): void
    {
        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/ForgotPasswordController.php');

        $this->artisan('generate:auth_forgot_password_controller', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    /** @test */
    public function it_can_generate_auth_forgot_password_controller_file(): void
    {
        $expected_path = $this->app->path('Http/Controllers/Customer/Auth/ForgotPasswordController.php');
        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/ForgotPasswordController.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:auth_forgot_password_controller', ['table' => 'customers', '--create' => true])
             ->assertSuccessful();
    }
}
