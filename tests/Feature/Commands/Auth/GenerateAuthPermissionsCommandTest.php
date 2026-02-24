<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateAuthPermissionsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteFile($this->app->databasePath('seeders/PermissionsSeeder.php'));
    }

    protected function tearDown(): void
    {
        $this->deleteFile($this->app->databasePath('seeders/PermissionsSeeder.php'));

        parent::tearDown();
    }

    public function test_it_can_generate_auth_permissions_output(): void
    {
        $expected_content = $this->getTestStubContents('seeders/_authPermissions.stub');

        $this->artisan('generate:auth_permissions', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_a_new_auth_permissions_file(): void
    {
        $expected_path = $this->app->databasePath('seeders/PermissionsSeeder.php');
        $expected_content = $this->getTestStubContents('seeders/AuthPermissionsSeederNew.php');

        $this->artisan('generate:auth_permissions', ['table' => 'customers', '--create' => true])
            ->assertSuccessful();

        $this->artisan('generate:auth_permissions', ['table' => 'public_users', '--create' => true])
             ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_append_permissions_to_an_existing_auth_permissions_seeder(): void
    {
        $expected_path = $this->app->databasePath('seeders/PermissionsSeeder.php');

        $this->copyFile($this->getTestStubPath('seeders/PermissionsSeeder.php'), $expected_path);
        $expected_content = $this->getTestStubContents('seeders/AuthPermissionsSeederExisting.php');

        $this->artisan('generate:auth_permissions', ['table' => 'customers', '--create' => true])
            ->assertSuccessful();

        $this->artisan('generate:auth_permissions', ['table' => 'public_users', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_does_not_duplicate_permissions_when_the_generate_command_is_called_multiple_times(): void
    {
        $expected_path = $this->app->databasePath('seeders/PermissionsSeeder.php');

        $this->copyFile($this->getTestStubPath('seeders/PermissionsSeeder.php'), $expected_path);
        $expected_content = $this->getTestStubContents('seeders/AuthPermissionsSeederExisting.php');

        $this->artisan('generate:auth_permissions', ['table' => 'customers', '--create' => true])
            ->assertSuccessful();

        $this->artisan('generate:auth_permissions', ['table' => 'public_users', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }
}
