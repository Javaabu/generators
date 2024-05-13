<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateAuthConfirmPasswordControllerCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_auth_confirm_password_controller_output(): void
    {
        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/ConfirmPasswordController.php');

        $this->artisan('generate:auth_confirm_password_controller', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    /** @test */
    public function it_can_generate_auth_confirm_password_controller_file(): void
    {
        $expected_path = $this->app->path('Http/Controllers/Customer/Auth/ConfirmPasswordController.php');
        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/ConfirmPasswordController.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:auth_confirm_password_controller', ['table' => 'customers', '--create' => true])
             ->assertSuccessful();
    }
}
