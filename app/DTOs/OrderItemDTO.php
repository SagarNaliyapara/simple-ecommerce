<?php

namespace App\DTOs;

readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public string $productName,
        public int $quantity,
        public float $price,
    ) {}
}
