<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class GenerateAuthConfigCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteFile($this->app->configPath('auth.php'));
    }

    protected function tearDown(): void
    {
        $this->deleteFile($this->app->configPath('auth.php'));

        parent::tearDown();
    }

    public function test_it_can_generate_auth_config_output(): void
    {
        $expected_content = '';

        $expected_content .= "// guards\n";
        $expected_content .= $this->getTestStubContents('config/auth/_guards.stub');

        $expected_content .= "// providers\n";
        $expected_content .= $this->getTestStubContents('config/auth/_providers.stub');

        $expected_content .= "// passwords\n";
        $expected_content .= $this->getTestStubContents('config/auth/_passwords.stub');

        $expected_content .= "// passport-guards\n";
        $expected_content .= $this->getTestStubContents('config/auth/_passport-guards.stub');

        $expected_content .= "// default-guard\n";
        $expected_content .= $this->getTestStubContents('config/auth/_default-guard.stub');

        $expected_content .= "// default-passwords\n";
        $expected_content .= $this->getTestStubContents('config/auth/_default-passwords.stub');

        $this->artisan('generate:auth_config', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_append_config_to_an_existing_auth_config_file(): void
    {
        $expected_path = $this->app->configPath('auth.php');

        $this->copyFile($this->getTestStubPath('config/auth/skeleton-auth.php'), $expected_path);
        $expected_content = $this->getTestStubContents('config/auth/models-auth.php');

        $this->artisan('generate:auth_config', ['table' => 'customers', '--create' => true])
            ->assertSuccessful();

        $this->artisan('generate:auth_config', ['table' => 'public_users', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }
}
