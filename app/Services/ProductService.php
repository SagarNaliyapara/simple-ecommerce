<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductService
{
    public function find(int $productId): ?Product
    {
        return Product::query()->find($productId);
    }

    public function findOrFail(int $productId): Product
    {
        return Product::query()->findOrFail($productId);
    }

    public function getAll(): Collection
    {
        return Product::all();
    }
}
