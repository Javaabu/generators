<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthForgotPasswordControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthForgotPasswordControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_forgot_password_controller(): void
    {
        $controller_generator = new AuthForgotPasswordControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/ForgotPasswordController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
