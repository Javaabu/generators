<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\ApiControllerGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class ApiControllerGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_api_controller_without_foreign_keys(): void
    {
        $api_generator = new ApiControllerGenerator('categories');

        $expected_content = $this->getTestStubContents('Controllers/Api/CategoriesController.php');
        $actual_content = $api_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_an_api_controller_with_foreign_keys(): void
    {
        $api_generator = new ApiControllerGenerator('products');

        $expected_content = $this->getTestStubContents('Controllers/Api/ProductsController.php');
        $actual_content = $api_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_api_routes(): void
    {
        $api_generator = new ApiControllerGenerator('categories');

        $expected_content = $this->getTestStubContents('routes/_api.stub');
        $actual_content = $api_generator->renderRoutes();

        $this->assertEquals($expected_content, $actual_content);
    }
}
