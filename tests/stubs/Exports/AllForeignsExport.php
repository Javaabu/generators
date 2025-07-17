<?php
/**
 * All Foreigns Export
 */

namespace App\Exports;

use App\Models\AllForeign;
use Javaabu\Exports\ModelExport;

class AllForeignsExport extends ModelExport
{
    public function modelClass(): string
    {
        return AllForeign::class;
    }

    public function relationsToInclude(): array
    {
        return [
            'category',
            'productSlug',
        ];
    }
}
