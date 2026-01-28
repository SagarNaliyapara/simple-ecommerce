<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function placeOrder(Collection $cartItems, int $userId): Collection
    {
        return DB::transaction(function () use ($cartItems, $userId) {

            $total = $cartItems->sum(fn ($item) => $item->quantity * $item->product->price);

            $order = Order::query()->create([
                'user_id' => $userId,
                'total_amount' => $total,
                'status' => 'pending',
            ]);

            $orderItems = $cartItems->map(fn ($item) => [
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            OrderItem::query()->insert($orderItems);

            $cartItems->each(fn ($item) => $item->product->decrement('stock_quantity', $item->quantity));

            $this->cartService->clearCart($userId);

            $threshold = config('app.low_stock_threshold', 3);

            return $cartItems
                ->map(fn ($item) => [
                    'name' => $item->product->name,
                    'stock_quantity' => $item->product->stock_quantity,
                ])
                ->filter(fn ($p) => $p['stock_quantity'] <= $threshold);
        });
    }

    public function getOrdersForUser(int $userId): Collection
    {
        return Order::query()
            ->with(['orderItems.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOrdersByDate(Carbon $date): Collection
    {
        return Order::query()
            ->with('orderItems.product')
            ->whereDate('created_at', $date)
            ->get();
    }
}
