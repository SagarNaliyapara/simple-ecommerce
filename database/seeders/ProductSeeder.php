<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'price' => 89.99,
                'stock_quantity' => 50,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Smart Watch Series 5',
                'price' => 299.99,
                'stock_quantity' => 30,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'USB-C Charging Cable',
                'price' => 19.99,
                'stock_quantity' => 100,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Portable Power Bank 20000mAh',
                'price' => 45.99,
                'stock_quantity' => 75,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Mechanical Gaming Keyboard',
                'price' => 129.99,
                'stock_quantity' => 40,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Wireless Gaming Mouse',
                'price' => 69.99,
                'stock_quantity' => 60,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => '4K Webcam with Microphone',
                'price' => 149.99,
                'stock_quantity' => 25,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Laptop Stand Aluminum',
                'price' => 39.99,
                'stock_quantity' => 55,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Bluetooth Speaker Waterproof',
                'price' => 79.99,
                'stock_quantity' => 45,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Phone Tripod with Remote',
                'price' => 34.99,
                'stock_quantity' => 70,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'LED Desk Lamp Smart',
                'price' => 54.99,
                'stock_quantity' => 35,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Noise Cancelling Earbuds',
                'price' => 159.99,
                'stock_quantity' => 20,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'External SSD 1TB',
                'price' => 119.99,
                'stock_quantity' => 0,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'Wireless Charging Pad',
                'price' => 29.99,
                'stock_quantity' => 85,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name' => 'HD Monitor 27 inch',
                'price' => 279.99,
                'stock_quantity' => 15,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
        ];

        Product::query()->insert($products);
    }
}
