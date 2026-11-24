<?php

namespace Javaabu\Generators\Tests\Unit\Support;

use Javaabu\Generators\Support\Imports;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImportsTest extends TestCase
{
    #[Test]
    public function it_returns_the_short_name_and_sorts_the_use_statements(): void
    {
        $imports = new Imports([], 'App\\Models');

        $this->assertEquals('Model', $imports->add('Illuminate\\Database\\Eloquent\\Model'));
        $this->assertEquals('Category', $imports->add('App\\Models\\Category'));
        $this->assertEquals('BelongsTo', $imports->add('\\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo'));

        // classes of the namespace itself need no import
        $this->assertEquals(
            "use Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;",
            $imports->render()
        );
    }

    #[Test]
    public function it_uses_the_qualified_name_for_a_clashing_basename(): void
    {
        $imports = new Imports;

        $this->assertEquals('Form', $imports->add('Javaabu\\Stencil\\Form'));
        $this->assertEquals('\\App\\Stencil\\Form', $imports->add('App\\Stencil\\Form'));
        $this->assertTrue($imports->has('Javaabu\\Stencil\\Form'));
    }
}
