<?php

namespace Javaabu\Generators\IconProviders;

use Illuminate\Support\Str;
use Javaabu\GeneratorHelpers\StringCaser;

abstract class BaseIconProvider
{
    public function findIconFor(string $word): string
    {
        $icon = $this->findFromPresetIcons($word);

        if (! $icon) {
            $icon = $this->findFromAllIcons($word);
        }

        if (! $icon) {
            $icon = $this->getDefaultIcon();
        }

        return $icon;
    }

    /**
     * Finds closest matching icon using Levenshtein distance
     * https://stackoverflow.com/questions/14421303/php-nearest-string-comparison
     *
     * @param string $word
     * @return string
     */
    protected function findFromAllIcons(string $word): string
    {
        $word = Str::lower($word);
        $icons = static::getIcons();

        // no shortest distance found, yet
        $shortest = 0;

        // loop through icons to find the closest
        foreach ($icons as $icon) {

            // calculate the distance between the input icon,
            // and the current icon
            $lev = 0;
            similar_text($word, $icon, $lev);

            // check for an exact match
            if ($lev == 1) {
                // closest icon is this one (exact match)
                $closest = $icon;
                $shortest = 1;

                // break out of the loop; we've found an exact match
                break;
            }

            // if this distance is less than the next found shortest
            // distance, OR if a next shortest icon has not yet been found
            if ($lev >= $shortest || $shortest == 0) {
                // set the closest match, and shortest distance
                $closest  = $icon;
                $shortest = $lev;
            }
        }

        //dd($shortest);

        return $closest ?? '';
    }

    protected function findFromPresetIcons(string $word): string
    {
        $word = StringCaser::pluralSnake($word);
        $presets = static::getPresetIcons();

        return $presets[$word] ?? '';
    }

    public function formatIcon(string $icon): string
    {
        return $this->getPrefix() . $icon;
    }

    public static function getPresetIcons(): array
    {
        return static::$preset_icons;
    }

    public static function getIcons(): array
    {
        return static::$icons;
    }

    public abstract function getPrefix(): string;

    public abstract function getDefaultIcon(): string;
}
