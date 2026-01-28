<?php

namespace App\Services;

use App\DTOs\ProductDTO;
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

    /** @return Collection<int, ProductDTO> */
    public function getAll(): Collection
    {
        return Product::all()->map(fn ($product) => new ProductDTO(
            id: $product->id,
            name: $product->name,
            price: (float) $product->price,
            stockQuantity: $product->stock_quantity,
        ));
    }
}
