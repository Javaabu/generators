<?php
/**
 * Categories Export
 */

namespace App\Exports;

use App\Models\Category;
use Javaabu\Exports\ModelExport;

class CategoriesExport extends ModelExport
{
    public function modelClass(): string
    {
        return Category::class;
    }
}
