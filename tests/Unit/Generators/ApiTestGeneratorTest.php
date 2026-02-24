<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\ApiTestGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class ApiTestGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_api_controller_test_without_foreign_keys(): void
    {
        $api_generator = new ApiTestGenerator('categories');

        $expected_content = $this->getTestStubContents('tests/Api/CategoriesControllerTest.stub');
        $actual_content = $api_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
