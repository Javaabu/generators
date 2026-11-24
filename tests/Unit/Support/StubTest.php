<?php

namespace Javaabu\Generators\Tests\Unit\Support;

use Javaabu\Generators\Support\Stub;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StubTest extends TestCase
{
    #[Test]
    public function it_indents_block_placeholders_to_their_line(): void
    {
        $template = "class A\n{\n    {{ body }}\n}";

        $this->assertEquals("class A\n{\n    one();\n\n    two();\n}", Stub::fill($template, ['body' => "one();\n\ntwo();"]));
    }

    #[Test]
    public function it_removes_the_line_of_an_empty_block(): void
    {
        $template = "class A\n{\n    {{ traits }}\n\n    {{ body }}\n}";

        $this->assertEquals("class A\n{\n    body();\n}", Stub::fill($template, ['traits' => '', 'body' => 'body();']));
    }

    #[Test]
    public function it_replaces_inline_placeholders_and_keeps_unknown_ones(): void
    {
        $this->assertEquals('Product {{ other }} {{ $blade }}', Stub::fill('{{ name }} {{ other }} {{ $blade }}', ['name' => 'Product']));
    }

    #[Test]
    public function it_indents_code(): void
    {
        $this->assertEquals("    a\n\n    b", Stub::indent("a\n\nb"));
        $this->assertEquals('        a', Stub::indent('a', 2));
    }
}
