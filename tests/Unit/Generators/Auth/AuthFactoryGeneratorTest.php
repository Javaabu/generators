<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth;

use Javaabu\Generators\Generators\Auth\AuthFactoryGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class AuthFactoryGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_factory_with_foreign_keys(): void
    {
        $factory_generator = new AuthFactoryGenerator('customers');

        $expected_content = $this->getTestStubContents('factories/CustomerFactory.php');
        $actual_content = $factory_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
