<?php

namespace Javaabu\Generators\Tests\Unit\Support;

use Javaabu\Generators\Support\PhpFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PhpFileTest extends TestCase
{
    protected string $provider = <<<'PHP'
    <?php

    namespace App\Providers;

    use Illuminate\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        public function boot(): void
        {
            //
        }
    }
    PHP;

    #[Test]
    public function it_adds_a_sorted_import_once(): void
    {
        $code = PhpFile::addImport($this->provider, 'Javaabu\\Permissions\\Permissions');
        $code = PhpFile::addImport($code, 'Javaabu\\Permissions\\Permissions');
        $code = PhpFile::addImport($code, 'App\\Models\\Product');

        $this->assertStringContainsString("use App\\Models\\Product;\nuse Illuminate\\Support\\ServiceProvider;\nuse Javaabu\\Permissions\\Permissions;\n", $code);
        $this->assertEquals(1, substr_count($code, 'use Javaabu\\Permissions\\Permissions;'));
    }

    #[Test]
    public function it_appends_to_a_method_once(): void
    {
        $code = PhpFile::appendToMethod($this->provider, 'boot', "Permissions::resource('products');");
        $code = PhpFile::appendToMethod($code, 'boot', "Permissions::resource('products');");
        $code = PhpFile::appendToMethod($code, 'boot', "Permissions::resource('tags');");

        $this->assertStringContainsString(
            "        //\n\n        Permissions::resource('products');\n\n        Permissions::resource('tags');\n    }",
            $code
        );
    }

    #[Test]
    public function it_fills_an_empty_method(): void
    {
        $code = PhpFile::appendToMethod("<?php\nclass A\n{\n    public function boot(): void\n    {\n    }\n}\n", 'boot', 'one();');

        $this->assertStringContainsString("    {\n        one();\n    }", $code);
    }

    #[Test]
    public function it_throws_when_the_method_is_missing(): void
    {
        $this->expectException(RuntimeException::class);

        PhpFile::appendToMethod($this->provider, 'register', 'one();');
    }

    #[Test]
    public function it_inserts_before_a_marker_with_its_indentation(): void
    {
        $code = "group(function () {\n    // marker\n});";

        $code = PhpFile::insertBeforeMarker($code, '// marker', "one();\ntwo();");
        $code = PhpFile::insertBeforeMarker($code, '// marker', "one();\ntwo();");

        $this->assertEquals("group(function () {\n    one();\n    two();\n\n    // marker\n});", $code);
    }
}
