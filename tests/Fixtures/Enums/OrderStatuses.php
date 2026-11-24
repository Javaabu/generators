<?php

namespace Javaabu\Generators\Tests\Fixtures\Enums;

enum OrderStatuses: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public static function getLabels(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
