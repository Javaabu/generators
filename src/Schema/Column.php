<?php

namespace Javaabu\Generators\Schema;

/**
 * A database column, normalized across drivers.
 */
final readonly class Column
{
    /**
     * @param  array<int, string>  $options
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $full_type = '',
        public bool $nullable = false,
        public mixed $default = null,
        public bool $auto_increment = false,
        public bool $unique = false,
        public ?string $comment = null,
        public ?int $length = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public bool $unsigned = false,
        public array $options = [],
    ) {}

    public function hasEnumClassComment(): bool
    {
        return str_starts_with((string) $this->comment, 'enum:');
    }

    public function enumClassFromComment(): ?string
    {
        if (! $this->hasEnumClassComment()) {
            return null;
        }

        return trim(substr((string) $this->comment, 5)) ?: null;
    }
}
