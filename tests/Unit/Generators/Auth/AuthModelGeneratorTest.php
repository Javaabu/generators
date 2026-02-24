<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\AuthModelGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthModelGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_model_with_foreign_keys(): void
    {
        $model_generator = new AuthModelGenerator('customers');

        $expected_content = $this->getTestStubContents('Models/Customer.php');
        $actual_content = $model_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_an_auth_model_without_foreign_keys(): void
    {
        $model_generator = new AuthModelGenerator('public_users', auth_name: 'portal');

        $expected_content = $this->getTestStubContents('Models/PublicUser.php');
        $actual_content = $model_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
