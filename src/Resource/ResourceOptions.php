<?php

namespace Javaabu\Generators\Resource;

/**
 * The developer's choices for a resource, from command options.
 */
final readonly class ResourceOptions
{
    /**
     * @param  array<int, string>  $columns  only these columns (empty for all)
     * @param  array<int, string>|null  $index  columns on the index table, null for the defaults
     * @param  array<int, string>|null  $form  fields in the form, null for the defaults
     * @param  array<int, string>|null  $show  fields on the show page, null for the defaults
     * @param  array<int, MediaCollection>  $media
     * @param  array<int, string>|bool|null  $translatable  translatable columns, true to detect them, false for none, null for config
     */
    public function __construct(
        public array $columns = [],
        public ?array $index = null,
        public ?array $form = null,
        public ?array $show = null,
        public ?string $parent = null,
        public array $media = [],
        public ?string $views = null,
        public array|bool|null $translatable = null,
        public ?string $user_type = null,
        public ?string $user_guard = null,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public static function fromArray(array $options): self
    {
        $list = function (mixed $value): ?array {
            if ($value === null || $value === '' || $value === false) {
                return null;
            }

            if (is_array($value)) {
                return array_values(array_filter(array_map('trim', $value)));
            }

            return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
        };

        $translatable = $options['translatable'] ?? null;

        if (is_string($translatable)) {
            $translatable = in_array($translatable, ['1', 'true', 'auto', ''], true) ? true : $list($translatable);
        }

        $media = $options['media'] ?? [];

        if (is_string($media)) {
            $media = MediaCollection::parse($media);
        }

        return new self(
            columns: $list($options['columns'] ?? null) ?? [],
            index: $list($options['index'] ?? null),
            form: $list($options['form'] ?? null),
            show: $list($options['show'] ?? null),
            parent: ($options['parent'] ?? null) ?: null,
            media: $media ?: [],
            views: ($options['views'] ?? null) ?: null,
            translatable: $translatable,
            user_type: ($options['user_type'] ?? null) ?: null,
            user_guard: ($options['user_guard'] ?? null) ?: null,
        );
    }
}
