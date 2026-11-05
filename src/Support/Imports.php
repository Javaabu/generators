<?php

namespace Javaabu\Generators\Support;

/**
 * Collects the classes a generated file uses and renders its use statements.
 */
class Imports
{
    /** @var array<string, string> class => alias */
    protected array $classes = [];

    /**
     * @param  array<int, string>  $classes
     */
    public function __construct(array $classes = [], protected ?string $namespace = null)
    {
        foreach ($classes as $class) {
            $this->add($class);
        }
    }

    /**
     * Import a class and return the name to use for it in the code.
     */
    public function add(string $class): string
    {
        $class = ltrim($class, '\\');
        $basename = class_basename($class);

        if (isset($this->classes[$class])) {
            return $this->classes[$class];
        }

        // a different class with the same basename: use the fully qualified name
        if (in_array($basename, $this->classes, true)) {
            return '\\' . $class;
        }

        $this->classes[$class] = $basename;

        return $basename;
    }

    public function has(string $class): bool
    {
        return isset($this->classes[ltrim($class, '\\')]);
    }

    public function merge(Imports $imports): static
    {
        foreach (array_keys($imports->classes) as $class) {
            $this->add($class);
        }

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        $classes = array_keys($this->classes);

        if ($this->namespace) {
            $classes = array_filter(
                $classes,
                fn (string $class) => ! (str_starts_with($class, $this->namespace . '\\') && substr_count($class, '\\') === substr_count($this->namespace, '\\') + 1)
            );
        }

        usort($classes, fn (string $a, string $b) => strcasecmp($a, $b));

        return array_values($classes);
    }

    public function render(): string
    {
        $classes = $this->all();

        if (! $classes) {
            return '';
        }

        return implode("\n", array_map(fn (string $class) => "use {$class};", $classes));
    }
}
