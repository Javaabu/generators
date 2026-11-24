<?php

namespace Javaabu\Generators\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocsTest extends TestCase
{
    protected function root(): string
    {
        return dirname(__DIR__, 2);
    }

    #[Test]
    public function it_links_every_docs_page_from_llms_txt(): void
    {
        $llms = file_get_contents($this->root() . '/llms.txt');

        preg_match_all('/\]\((docs\/[^)]+)\)/', $llms, $matches);

        foreach ($matches[1] as $link) {
            $this->assertFileExists($this->root() . '/' . $link, "llms.txt links to a missing page: {$link}");
        }

        $skip = ['docs/about-us.md', 'docs/changelog.md', 'docs/contributing-to-docs.md'];

        foreach (glob($this->root() . '/docs/{,*/}*.md', GLOB_BRACE) as $page) {
            $relative = substr($page, strlen($this->root()) + 1);

            if (in_array($relative, $skip, true)) {
                continue;
            }

            $this->assertContains($relative, $matches[1], "llms.txt doesn't link to {$relative}");
        }
    }

    #[Test]
    public function it_links_to_existing_pages_from_the_docs(): void
    {
        foreach (glob($this->root() . '/docs/{,*/}*.md', GLOB_BRACE) as $page) {
            preg_match_all('/\]\(((?!https?:|#|\.\.\/\.\.\/)[^)#]+\.md)(#[^)]*)?\)/', file_get_contents($page), $matches);

            foreach ($matches[1] as $link) {
                $this->assertFileExists(dirname($page) . '/' . $link, basename($page) . " links to a missing page: {$link}");
            }
        }
    }
}
