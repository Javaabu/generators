<?php

namespace Javaabu\Generators\Support;

use Illuminate\Support\Str;

class Naming
{
    /**
     * The belongsTo relation of a foreign key: category_id gives category,
     * product_slug (to products.slug) gives product, owner gives owner.
     */
    public static function belongsTo(string $column, string $foreign_table): string
    {
        if (Str::endsWith($column, '_id') && ($name = Str::beforeLast($column, '_id'))) {
            return Str::camel($name);
        }

        $singular = Str::singular(Str::snake($foreign_table));

        if (Str::startsWith($column, $singular . '_')) {
            return Str::camel($singular);
        }

        return Str::camel($column);
    }
}
