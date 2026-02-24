<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth;

use Illuminate\Support\Facades\Config;
use Javaabu\Generators\Generators\Auth\AuthConfigGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class AuthConfigGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_auth_guards_config(): void
    {
        $config_generator = new AuthConfigGenerator('customers');

        $expected_content = $this->getTestStubContents('config/auth/_guards.stub');
        $actual_content = $config_generator->renderGuards();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_auth_providers_config(): void
    {
        $config_generator = new AuthConfigGenerator('customers');

        $expected_content = $this->getTestStubContents('config/auth/_providers.stub');
        $actual_content = $config_generator->renderProviders();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_auth_passwords_config(): void
    {
        $config_generator = new AuthConfigGenerator('customers');

        $expected_content = $this->getTestStubContents('config/auth/_passwords.stub');
        $actual_content = $config_generator->renderPasswords();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_auth_passport_guards_config(): void
    {
        $config_generator = new AuthConfigGenerator('customers');

        $expected_content = $this->getTestStubContents('config/auth/_passport-guards.stub');
        $actual_content = $config_generator->renderPassportGuards();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_auth_default_guard_config(): void
    {
        $config_generator = new AuthConfigGenerator('customers');

        $expected_content = $this->getTestStubContents('config/auth/_default-guard.stub');
        $actual_content = $config_generator->renderDefaultGuard();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_auth_default_passwords_config(): void
    {
        $config_generator = new AuthConfigGenerator('customers');

        $expected_content = $this->getTestStubContents('config/auth/_default-passwords.stub');
        $actual_content = $config_generator->renderDefaultPasswords();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_determine_whether_default_guard_should_be_generated(): void
    {
        $config_generator = new AuthConfigGenerator('customers');

        $this->assertTrue($config_generator->shouldRenderDefaultGuard());

        Config::set('auth.defaults.guard', 'web_admin');

        $this->assertTrue($config_generator->shouldRenderDefaultGuard());

        Config::set('auth.defaults.guard', 'web_customer');

        $this->assertFalse($config_generator->shouldRenderDefaultGuard());
    }
}
