<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthHomeControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthHomeControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_home_controller(): void
    {
        $controller_generator = new AuthHomeControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/Customer/HomeController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
