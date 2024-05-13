<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthLoginControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthLoginControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_an_auth_login_controller(): void
    {
        $controller_generator = new AuthLoginControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/LoginController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
