<?php

namespace Javaabu\Generators\Generators;

/**
 * Adds Dhivehi for the generic phrases the generated code uses to the app's
 * lang/dv.json. Phrases the app already translates are kept.
 */
class TranslationsGenerator extends Generator
{
    public function files(): array
    {
        $path = lang_path('dv.json');

        return [new GeneratedFile($path, json_encode($this->phrases(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), function (string $json) {
            $current = $json === '' ? [] : (json_decode($json, true) ?: []);

            $merged = $current + $this->phrases();

            if ($merged === $current && $json !== '') {
                return $json;
            }

            return json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        }, 'Dhivehi translations')];
    }

    /**
     * @return array<string, string>
     */
    public function phrases(): array
    {
        $phrases = (array) trans('generators::phrases', [], 'dv');

        $used = ['Search', 'Created at', 'Updated at', 'Edit', 'View', 'Delete', 'Breadcrumb', 'ID'];

        if ($this->resource->hasSoftDeletes()) {
            $used = [...$used, 'Restore', 'Delete permanently'];
        }

        if ($this->resource->hasMedia()) {
            $used[] = 'Media';
        }

        return array_intersect_key($phrases, array_flip($used));
    }
}
