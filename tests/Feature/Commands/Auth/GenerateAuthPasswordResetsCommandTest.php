<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateAuthPasswordResetsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_password_resets_output(): void
    {
        $expected_content = $this->getTestStubContents('migrations/create_customer_password_reset_tokens_table.php');

        $this->artisan('generate:auth_password_resets', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_password_resets_migration_file(): void
    {
        $this->travelTo('2024-04-23 12:43:31');

        $expected_path = $this->app->databasePath('migrations/2024_04_23_124331_create_customer_password_reset_tokens_table.php');
        $expected_content = $this->getTestStubContents('migrations/create_customer_password_reset_tokens_table.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:auth_password_resets', ['table' => 'customers', '--create' => true])
             ->assertSuccessful();
    }
}
