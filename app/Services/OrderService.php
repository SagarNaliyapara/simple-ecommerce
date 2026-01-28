<?php

namespace App\Services;

use App\DTOs\LowStockProductDTO;
use App\DTOs\OrderDTO;
use App\DTOs\OrderItemDTO;
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

    /** @return Collection<int, LowStockProductDTO> */
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

            $cartItems->each(function ($item) {
                $item->product->decrement('stock_quantity', $item->quantity);
                $item->product->refresh();
            });

            $this->cartService->clearCart($userId);

            return $cartItems
                ->filter(fn ($item) => $item->product->stock_quantity <= $item->product->low_stock_threshold)
                ->map(fn ($item) => new LowStockProductDTO(
                    name: $item->product->name,
                    stockQuantity: $item->product->stock_quantity,
                ));
        });
    }

    /** @return Collection<int, OrderDTO> */
    public function getOrdersForUser(int $userId): Collection
    {
        return Order::query()
            ->with(['orderItems.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($order) => new OrderDTO(
                id: $order->id,
                createdAt: $order->created_at,
                status: $order->status,
                totalAmount: (float) $order->total_amount,
                items: $order->orderItems->map(fn ($item) => new OrderItemDTO(
                    productId: $item->product_id,
                    productName: $item->product->name,
                    quantity: $item->quantity,
                    price: (float) $item->price,
                )),
            ));
    }

    /** @return Collection<int, OrderDTO> */
    public function getOrdersByDate(Carbon $date): Collection
    {
        return Order::query()
            ->with('orderItems.product')
            ->whereDate('created_at', $date)
            ->get()
            ->map(fn ($order) => new OrderDTO(
                id: $order->id,
                createdAt: $order->created_at,
                status: $order->status,
                totalAmount: (float) $order->total_amount,
                items: $order->orderItems->map(fn ($item) => new OrderItemDTO(
                    productId: $item->product_id,
                    productName: $item->product->name,
                    quantity: $item->quantity,
                    price: (float) $item->price,
                )),
            ));
    }
}
