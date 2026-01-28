<?php

namespace App\DTOs;

readonly class LowStockProductDTO
{
    public function __construct(
        public string $name,
        public int $stockQuantity,
    ) {}
}
