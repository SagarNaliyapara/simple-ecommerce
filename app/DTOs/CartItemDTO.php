<?php

namespace App\DTOs;

readonly class CartItemDTO
{
    public function __construct(
        public int $id,
        public int $quantity,
        public string $productName,
        public float $productPrice,
        public int $productStockQuantity,
    ) {}
}
