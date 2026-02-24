<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\ModelGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class ModelGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_foreign_key_relation(): void
    {
        $model_generator = new ModelGenerator('products');

        $expected_content = $this->getTestStubContents('Models/_categoryForeignKey.stub');
        $actual_content = $model_generator->renderModelForeignKey('category_id', $model_generator->getField('category_id'));

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_date_mutator(): void
    {
        $model_generator = new ModelGenerator('products');

        $expected_content = $this->getTestStubContents('Models/_dateMutator.stub');
        $actual_content = $model_generator->renderModelDateMutator('published_at', $model_generator->getField('published_at'));

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_nullable_date_mutator(): void
    {
        $model_generator = new ModelGenerator('posts');

        $expected_content = $this->getTestStubContents('Models/_nullableDateMutator.stub');
        $actual_content = $model_generator->renderModelDateMutator('published_at', $model_generator->getField('published_at'));

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_model_with_foreign_keys(): void
    {
        $model_generator = new ModelGenerator('products');

        $expected_content = $this->getTestStubContents('Models/Product.php');
        $actual_content = $model_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_model_without_foreign_keys(): void
    {
        $model_generator = new ModelGenerator('categories');

        $expected_content = $this->getTestStubContents('Models/Category.php');
        $actual_content = $model_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_model_with_multiple_foreign_keys(): void
    {
        $model_generator = new ModelGenerator('orders');

        $expected_content = $this->getTestStubContents('Models/Order.php');
        $actual_content = $model_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
