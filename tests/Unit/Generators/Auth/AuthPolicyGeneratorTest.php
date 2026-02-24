<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth;

use Javaabu\Generators\Generators\Auth\AuthPolicyGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class AuthPolicyGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_auth_policy_for_soft_delete_model(): void
    {
        $policy_generator = new AuthPolicyGenerator('customers');

        $expected_content = $this->getTestStubContents('Policies/CustomerPolicy.php');
        $actual_content = $policy_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
