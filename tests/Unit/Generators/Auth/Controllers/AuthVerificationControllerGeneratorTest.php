<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\Controllers\AuthVerificationControllerGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthVerificationControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_verification_controller(): void
    {
        $controller_generator = new AuthVerificationControllerGenerator('customers');

        $expected_content = $this->getTestStubContents('Controllers/Customer/Auth/VerificationController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
