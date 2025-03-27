<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\TestGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class TestGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_input_errors(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_errors.stub');
        $actual_content = $test_generator->renderErrors();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_wrong_inputs(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_wrongInputs.stub');
        $actual_content = $test_generator->renderWrongInputs();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_wrong_db_values(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_wrongDbValues.stub');
        $actual_content = $test_generator->renderWrongDbValues();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_correct_inputs(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_correctInputs.stub');
        $actual_content = $test_generator->renderCorrectInputs();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_correct_db_values(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_correctDbValues.stub');
        $actual_content = $test_generator->renderCorrectDbValues();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_different_correct_inputs(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_differentCorrectInputs.stub');
        $actual_content = $test_generator->renderDifferentCorrectInputs();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_different_correct_db_values(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_differentCorrectDbValues.stub');
        $actual_content = $test_generator->renderDifferentCorrectDbValues();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_factory_inputs(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_factoryInputs.stub');
        $actual_content = $test_generator->renderFactoryInputs();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_factory_db_values(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/_factoryDbValues.stub');
        $actual_content = $test_generator->renderFactoryDbValues();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_test_with_foreign_keys(): void
    {
        $test_generator = new TestGenerator('products');

        $expected_content = $this->getTestStubContents('tests/ProductsControllerTest.stub');
        $actual_content = $test_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_test_without_foreign_keys(): void
    {
        $test_generator = new TestGenerator('categories');

        $expected_content = $this->getTestStubContents('tests/CategoriesControllerTest.stub');
        $actual_content = $test_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_test_with_multiple_foreign_keys(): void
    {
        $test_generator = new TestGenerator('orders');

        $expected_content = $this->getTestStubContents('tests/OrdersControllerTest.stub');
        $actual_content = $test_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_test_where_the_enum_has_only_one_case(): void
    {
        $test_generator = new TestGenerator('single_value_enum_orders');

        $expected_content = $this->getTestStubContents('tests/SingleValueEnumOrdersControllerTest.stub');
        $actual_content = $test_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
