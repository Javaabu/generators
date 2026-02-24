<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthConfirmPasswordControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthConfirmPasswordControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_confirm_password_controller(): void
    {
        $controller_generator = new AuthConfirmPasswordControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/ConfirmPasswordController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
