<?php

namespace App\DTOs;

readonly class ProductDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public float $price,
        public int $stockQuantity,
    ) {}
}
