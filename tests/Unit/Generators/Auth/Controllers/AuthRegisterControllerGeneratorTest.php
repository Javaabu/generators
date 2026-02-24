<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthRegisterControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthRegisterControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_register_controller(): void
    {
        $controller_generator = new AuthRegisterControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/RegisterController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_register_assignments(): void
    {
        $controller_generator = new AuthRegisterControllerGenerator('customers');

        $expected_content = '$customer->category()->associate($data[\'category\']);';
        $actual_content = $controller_generator->renderRegisterAssignment('category_id');

        $this->assertEquals($expected_content, $actual_content);

        $expected_content = '$customer->on_sale = $data[\'on_sale\'] ?? false;';
        $actual_content = $controller_generator->renderRegisterAssignment('on_sale');

        $this->assertEquals($expected_content, $actual_content);

        $expected_content = '$customer->designation = $data[\'designation\'];';
        $actual_content = $controller_generator->renderRegisterAssignment('designation');

        $this->assertEquals($expected_content, $actual_content);
    }
}
