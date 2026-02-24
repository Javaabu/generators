<?php

namespace Javaabu\Generators\Tests\Unit;

use Javaabu\Generators\FieldTypes\StringField;
use Javaabu\Generators\Tests\TestCase;

class FakeStringField extends StringField
{
    public function shouldRenderInputInline(): bool
    {
        return false;
    }

    public function getFormComponentAttributes(): array
    {
        return ['maxlength' => 255];
    }
}

class FieldTest extends TestCase
{
    public function test_it_can_render_field_assignment(): void
    {
        $this->assertEquals('slug = $request->input(\'slug\')', (new FakeStringField('slug'))->renderAssignment());
    }

    public function test_it_can_render_field_attributes(): void
    {
        $this->assertEquals('maxlength="255" required :inline="false" show-placeholder', (new FakeStringField('slug'))->renderFormComponentAttributes());
    }

    public function test_it_can_render_a_component(): void
    {
        $this->assertEquals('<x-forms::text name="slug" maxlength="255" required :inline="false" show-placeholder />', (new FakeStringField('slug'))->renderFormComponent());
    }
}
