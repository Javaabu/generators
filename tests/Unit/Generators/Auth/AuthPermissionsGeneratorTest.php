<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth;

use Javaabu\Generators\Generators\Auth\AuthPermissionsGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class AuthPermissionsGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_auth_permissions_for_soft_delete_model(): void
    {
        $permissions_generator = new AuthPermissionsGenerator('customers');

        $expected_content = $this->getTestStubContents('seeders/_authPermissions.stub');
        $actual_content = $permissions_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
