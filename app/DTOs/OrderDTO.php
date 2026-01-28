<?php

namespace App\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class OrderDTO
{
    public function __construct(
        public int $id,
        public Carbon $createdAt,
        public string $status,
        public float $totalAmount,
        public Collection $items,
    ) {}
}
