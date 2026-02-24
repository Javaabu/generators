<?php

namespace Javaabu\Generators\Tests\Unit;

use Javaabu\Generators\FieldTypes\BooleanField;
use Javaabu\Generators\Tests\TestCase;

class BooleanFieldTest extends TestCase
{
    public function test_it_can_generate_boolean_assignment_from_request(): void
    {
        $this->assertEquals('on_sale = $request->input(\'on_sale\', false)', (new BooleanField('on_sale'))->renderAssignment());
    }

    public function test_it_can_generate_boolean_assignment_from_array(): void
    {
        $this->assertEquals('on_sale = $input[\'on_sale\'] ?? false', (new BooleanField('on_sale'))->renderAssignment('$input[', ']'));
    }
}
