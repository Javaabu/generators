<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\ControllerGenerator;
use Javaabu\Generators\Tests\InteractsWithDatabase;
use Javaabu\Generators\Tests\TestCase;

class ControllerGeneratorTest extends TestCase
{
    use InteractsWithDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /** @test */
    public function it_can_generate_foreign_key_assignment(): void
    {
        $controller_generator = new ControllerGenerator('products');

        $expected_content = $this->getTestStubContents('Controllers/_categoryForeignKey.stub');
        $actual_content = $controller_generator->renderForeignKey('category_id', $controller_generator->getField('category_id'));

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_boolean_assignment(): void
    {
        $controller_generator = new ControllerGenerator('products');

        $expected_content = $this->getTestStubContents('Controllers/_onSaleBoolean.stub');
        $actual_content = $controller_generator->renderBoolean('on_sale', $controller_generator->getField('on_sale'));

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_an_orderby(): void
    {
        $controller_generator = new ControllerGenerator('products');

        $expected_content = $this->getTestStubContents('Controllers/_orderBy.stub');
        $actual_content = $controller_generator->renderOrderby('created_at');

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_controller_without_foreign_keys(): void
    {
        $controller_generator = new ControllerGenerator('categories');

        $expected_content = $this->getTestStubContents('Controllers/CategoriesController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_controller_with_foreign_keys(): void
    {
        $controller_generator = new ControllerGenerator('products');

        $expected_content = $this->getTestStubContents('Controllers/ProductsController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_controller_with_multiple_foreign_keys(): void
    {
        $controller_generator = new ControllerGenerator('orders');

        $expected_content = $this->getTestStubContents('Controllers/OrdersController.php');
        $actual_content = $controller_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_controller_eager_loads(): void
    {
        $controller_generator = new ControllerGenerator('orders');

        $expected_content = $this->getTestStubContents('Controllers/_ordersEagerLoads.stub');
        $actual_content = $controller_generator->renderEagerLoads(["'category'", "'productSlug'"]);

        $this->assertEquals($expected_content, $actual_content);
    }
}
