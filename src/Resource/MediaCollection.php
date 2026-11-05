<?php

namespace Javaabu\Generators\Resource;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * A javaabu/mediapicker attachment collection of a resource.
 */
final readonly class MediaCollection
{
    public const array TYPES = ['image', 'video', 'audio', 'document', 'archive'];

    public function __construct(
        public string $name,
        public string $type = 'image',
        public bool $multiple = false,
    ) {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Unknown media type [{$type}] for [{$name}]. Use one of: " . implode(', ', self::TYPES));
        }
    }

    /**
     * Parse "featured_image:image,gallery:image:multiple,documents:document:multiple".
     *
     * @return array<int, MediaCollection>
     */
    public static function parse(string $definition): array
    {
        $collections = [];

        foreach (array_filter(array_map('trim', explode(',', $definition))) as $part) {
            $segments = array_map('trim', explode(':', $part));
            $name = Str::snake(array_shift($segments));

            $type = 'image';
            $multiple = false;

            foreach ($segments as $segment) {
                if ($segment === 'multiple') {
                    $multiple = true;

                    continue;
                }

                $type = $segment;
            }

            $collections[] = new self($name, $type, $multiple);
        }

        return $collections;
    }

    public function label(): string
    {
        return Str::ucfirst(str_replace('_', ' ', $this->name));
    }
}
