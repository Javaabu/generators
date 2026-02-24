<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthUpdatePasswordControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthUpdatePasswordControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_update_password_controller(): void
    {
        $controller_generator = new AuthUpdatePasswordControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/UpdatePasswordController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
