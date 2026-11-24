<?php

namespace Javaabu\Generators\Generators;

use Closure;

/**
 * A file a generator writes. A file with a merge callback is added to an
 * existing file (routes, service providers, translations) instead of
 * replacing it.
 */
final readonly class GeneratedFile
{
    /**
     * @param  (Closure(string): string)|null  $merge  given the current contents, returns the new contents
     */
    public function __construct(
        public string $path,
        public string $contents,
        public ?Closure $merge = null,
        public string $description = '',
    ) {}

    public function isMerge(): bool
    {
        return $this->merge !== null;
    }

    public function mergeInto(string $existing): string
    {
        return ($this->merge)($existing);
    }

    public function relativePath(): string
    {
        $base = rtrim(base_path(), '/') . '/';

        return str_starts_with($this->path, $base) ? substr($this->path, strlen($base)) : $this->path;
    }
}
