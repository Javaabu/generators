<?php

namespace App\Enums;

enum ProductTypes: string
{
    case Physical = 'physical';
    case Digital = 'digital';

    public function getLabel(): string
    {
        return match ($this) {
            self::Physical => __('Physical'),
            self::Digital => __('Digital'),
        };
    }
}
