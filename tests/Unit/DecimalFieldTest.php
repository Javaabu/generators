<?php

namespace Javaabu\Generators\Tests\Unit;

use Javaabu\Generators\FieldTypes\DecimalField;
use Javaabu\Generators\Tests\TestCase;

class DecimalFieldTest extends TestCase
{
    public function test_it_can_generate_the_step_attribute(): void
    {
        $this->assertEquals('0.01', (new DecimalField('price', 14, 2))->getStep());
        $this->assertEquals('1', (new DecimalField('price', 14, 0))->getStep());
        $this->assertEquals('0.001', (new DecimalField('price', 14, 3))->getStep());
        $this->assertEquals('0.1', (new DecimalField('price', 14, 1))->getStep());
    }
}
