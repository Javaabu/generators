<?php

namespace Javaabu\Generators\Tests\TestSupport\Enums;

enum SingleCaseOrderStatuses: string
{
    case Pending = 'pending';

    public static function getLabels(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
