<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\Auth\AuthRequestGenerator;
use Javaabu\Generators\Tests\TestCase;

class AuthRequestGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_the_base_rules_statement(): void
    {
        $request_generator = new AuthRequestGenerator('customers');

        $this->assertEquals('$this->baseRules()', $request_generator->renderBaseRulesStatement());

        $request_generator = new AuthRequestGenerator('public_users');

        $this->assertEquals('$this->baseRules(false, false)', $request_generator->renderBaseRulesStatement());
    }

    public function test_it_can_generate_an_auth_model_request_with_one_unique_rule(): void
    {
        $request_generator = new AuthRequestGenerator('customers');

        $expected_content = $this->getTestStubContents('Requests/CustomersRequest.php');
        $actual_content = $request_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_an_auth_model_request_with_no_unique_rules(): void
    {
        $request_generator = new AuthRequestGenerator('public_users', auth_name: 'portal');

        $expected_content = $this->getTestStubContents('Requests/PublicUsersRequest.php');
        $actual_content = $request_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
