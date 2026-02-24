<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_controller_with_foreign_keys(): void
    {
        $controller_generator = new AuthControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/CustomersController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
