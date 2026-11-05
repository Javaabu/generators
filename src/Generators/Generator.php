<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Resource\ResourceDefinition;

abstract class Generator
{
    public function __construct(
        protected ResourceDefinition $resource,
    ) {}

    /**
     * @return array<int, GeneratedFile>
     */
    abstract public function files(): array;

    public function getResource(): ResourceDefinition
    {
        return $this->resource;
    }

    /**
     * Everything the generator writes, for printing.
     */
    public function render(): string
    {
        $output = [];

        foreach ($this->files() as $file) {
            $output[] = count($this->files()) > 1 ? "// {$file->relativePath()}\n{$file->contents}" : $file->contents;
        }

        return implode("\n", $output);
    }

    /**
     * Join PHP blocks with a blank line between them.
     *
     * @param  array<int, string|null>  $blocks
     */
    protected function blocks(array $blocks): string
    {
        return implode("\n\n", array_filter($blocks, fn (?string $block) => $block !== null && $block !== ''));
    }

    protected function namespaceOf(string $class): string
    {
        return substr($class, 0, (int) strrpos($class, '\\'));
    }
}
