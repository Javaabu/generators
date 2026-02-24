<?php

namespace Javaabu\Generators\Tests\Unit;

use Javaabu\GeneratorHelpers\StubRenderer;
use Javaabu\Generators\Tests\TestCase;

class StubRendererTest extends TestCase
{
    public function test_it_can_replace_single_names(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('SingleNameCases.php');
        $actual_content = $renderer->replaceFileNames($this->getTestStubPath('NameCases.stub'), 'category');

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_replace_multiple_names(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('MultipleNameCases.php');
        $actual_content = $renderer->replaceFileNames($this->getTestStubPath('NameCases.stub'), 'form_input_field');

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_replace_multiple_names_with_suffix(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('MultipleNameCases.php');
        $actual_content = $renderer->replaceFileNames($this->getTestStubPath('NameCasesSuffix.stub'), 'form_input_field', 'Model');

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_replace_multiple_names_in_studly_format(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('MultipleNameCases.php');
        $actual_content = $renderer->replaceFileNames($this->getTestStubPath('NameCases.stub'), 'FormInputField');

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_replace_multiple_names_in_camel_format(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('MultipleNameCases.php');
        $actual_content = $renderer->replaceFileNames($this->getTestStubPath('NameCases.stub'), 'formInputField');

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_append_to_a_stub_without_removing_marker(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('AfterAppend.php');
        $actual_content = $renderer->appendToFile($this->getTestStubPath('BeforeAppend.php'), "    'product',\n", "// append here - DONOT REMOVE\n");

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_append_to_a_stub_with_removing_marker(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('AfterAppendMarkerRemoved.php');
        $actual_content = $renderer->appendToFile($this->getTestStubPath('BeforeAppend.php'), "'product',\n", "// append here - DONOT REMOVE\n", false);

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_add_indentation_to_multiple_lines(): void
    {
        $renderer = $this->getRenderer();

        $expected_content = $this->getTestStubContents('Controllers/_onSaleBooleanIndented.stub');
        $actual_content = $renderer->addIndentation($this->getTestStubContents('Controllers/_onSaleBoolean.stub'), 1);

        $this->assertEquals($expected_content, $actual_content);

        $expected_content = $this->getTestStubContents('Controllers/_onSaleBoolean.stub');
        $actual_content = $renderer->addIndentation($this->getTestStubContents('Controllers/_onSaleBoolean.stub'), 0);

        $this->assertEquals($expected_content, $actual_content);
    }

    protected function getRenderer(): StubRenderer
    {
        return $this->app->make(StubRenderer::class);
    }

}
