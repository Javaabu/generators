<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\RoutesGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class RoutesGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_routes_for_soft_delete_model(): void
    {
        $routes_generator = new RoutesGenerator('products');

        $expected_content = $this->getTestStubContents('routes/_adminSoftDeletes.stub');
        $actual_content = $routes_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_routes_for_a_none_soft_delete(): void
    {
        $routes_generator = new RoutesGenerator('categories');

        $expected_content = $this->getTestStubContents('routes/_admin.stub');
        $actual_content = $routes_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
