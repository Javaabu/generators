<?php
/**
 * Products Export
 */

namespace App\Exports;

use App\Models\Product;
use Javaabu\Exports\ModelExport;

class ProductsExport extends ModelExport
{
    public function modelClass(): string
    {
        return Product::class;
    }

    public function map(): array
    {
        return [];
    }
}
