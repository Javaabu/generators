<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\PolicyGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class PolicyGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_a_policy_for_soft_delete_model(): void
    {
        $policy_generator = new PolicyGenerator('products');

        $expected_content = $this->getTestStubContents('Policies/ProductPolicy.php');
        $actual_content = $policy_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_policy_for_a_none_soft_delete(): void
    {
        $policy_generator = new PolicyGenerator('categories');

        $expected_content = $this->getTestStubContents('Policies/CategoryPolicy.php');
        $actual_content = $policy_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
