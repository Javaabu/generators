<?php

namespace Javaabu\Generators\Support;

/**
 * Detects javaabu/translatable in the app. The generators only write
 * translatable code when the package is installed.
 */
class Translations
{
    protected static ?bool $installed = null;

    public static function installed(): bool
    {
        if (static::$installed !== null) {
            return static::$installed;
        }

        return class_exists('Javaabu\\Translatable\\Translatable');
    }

    /**
     * Pretend javaabu/translatable is (or isn't) installed, for tests.
     */
    public static function fake(?bool $installed): void
    {
        static::$installed = $installed;
    }
}
