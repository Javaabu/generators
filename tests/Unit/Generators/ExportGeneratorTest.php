<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\ExportGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class ExportGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_an_export(): void
    {
        $export_generator = new ExportGenerator('categories');

        $expected_content = $this->getTestStubContents('Exports/CategoriesExport.php');
        $actual_content = $export_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_an_export_with_relations(): void
    {
        $export_generator = new ExportGenerator('all_foreigns');

        $expected_content = $this->getTestStubContents('Exports/AllForeignsExport.php');
        $actual_content = $export_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
