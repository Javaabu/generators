<?php

namespace Javaabu\Generators\Support;

use Javaabu\GeneratorHelpers\StubRenderer;

/**
 * Renders a stub: the {{pluralStudly}}-style name placeholders of
 * javaabu/generator-helpers, then {{ placeholder }} blocks.
 *
 * A block placeholder alone on its line is indented to that line, and the
 * line is removed when the block is empty. Stubs can be published and
 * overridden in stubs/vendor/generators.
 */
class Stub
{
    /** @var array<string, string> */
    protected array $values = [];

    protected ?string $name = null;

    public function __construct(
        protected string $stub,
    ) {}

    public static function make(string $stub): static
    {
        return new static($stub);
    }

    /**
     * Replace the name placeholders with the cases of this name.
     */
    public function names(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function with(array $values): static
    {
        foreach ($values as $key => $value) {
            $this->values[$key] = (string) $value;
        }

        return $this;
    }

    public function render(): string
    {
        /** @var StubRenderer $renderer */
        $renderer = app(StubRenderer::class);

        $template = $renderer->loadStub("generators::{$this->stub}");

        if ($this->name !== null) {
            $template = $renderer->replaceNames($this->name, $template);
        }

        return static::fill($template, $this->values);
    }

    /**
     * @param  array<string, string>  $values
     */
    public static function fill(string $template, array $values): string
    {
        $template = str_replace("\r\n", "\n", $template);

        $lines = explode("\n", $template);
        $output = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\s*)\{\{ (\w+) \}\}\s*$/', $line, $matches) && array_key_exists($matches[2], $values)) {
                $value = $values[$matches[2]];

                if ($value === '') {
                    continue;
                }

                foreach (explode("\n", $value) as $value_line) {
                    $output[] = $value_line === '' ? '' : $matches[1] . $value_line;
                }

                continue;
            }

            $output[] = preg_replace_callback(
                '/\{\{ (\w+) \}\}/',
                fn (array $match) => array_key_exists($match[1], $values) ? $values[$match[1]] : $match[0],
                $line
            );
        }

        $rendered = implode("\n", $output);

        // collapse blank lines left by empty blocks
        $rendered = preg_replace("/\n{3,}/", "\n\n", $rendered);
        $rendered = preg_replace("/(\{\n)\n+/", '$1', $rendered);
        $rendered = preg_replace("/\n\n+(\s*\})/", "\n$1", $rendered);

        return $rendered;
    }

    /**
     * Indent every non-empty line.
     */
    public static function indent(string $code, int $levels = 1): string
    {
        $spaces = str_repeat('    ', $levels);

        return implode("\n", array_map(
            fn (string $line) => $line === '' ? '' : $spaces . $line,
            explode("\n", $code)
        ));
    }
}
