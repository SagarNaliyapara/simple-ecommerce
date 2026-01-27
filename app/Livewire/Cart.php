<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Cart extends Component
{
    public function updateQuantity($cartItemId, $action): void
    {
        $cartItem = CartItem::query()
            ->where('id', $cartItemId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $product = $cartItem->product;

        if ($action === 'increment') {
            if ($cartItem->quantity >= $product->stock_quantity) {
                session()->flash('error', 'Cannot add more. Stock limit reached.');

                return;
            }
            $cartItem->increment('quantity');
            session()->flash('success', 'Quantity updated.');
        } elseif ($action === 'decrement') {
            if ($cartItem->quantity > 1) {
                $cartItem->decrement('quantity');
                session()->flash('success', 'Quantity updated.');
            } else {
                $cartItem->delete();
                session()->flash('success', 'Item removed from cart.');
            }
        }

        $this->dispatch('cart-updated');
    }

    public function removeItem($cartItemId): void
    {
        CartItem::query()
            ->where('id', $cartItemId)
            ->where('user_id', auth()->id())
            ->delete();

        session()->flash('success', 'Item removed from cart.');
        $this->dispatch('cart-updated');
    }

    public function proceedToOrder(): void
    {
        $userId = auth()->id();

        $cartItems = CartItem::query()
            ->where('user_id', $userId)
            ->with('product:id,name,price,stock_quantity')
            ->lockForUpdate()
            ->get();

        if ($cartItems->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');

            return;
        }

        $hasOutOfStock = $cartItems->contains(fn ($item) => $item->quantity > $item->product->stock_quantity
        );

        if ($hasOutOfStock) {
            session()->flash('error', 'One or more products are out of stock.');

            return;
        }

        DB::transaction(function () use ($cartItems, $userId) {

            $total = $cartItems->sum(fn ($item) => $item->quantity * $item->product->price
            );

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

            CartItem::query()->where('user_id', $userId)->delete();
        });

        session()->flash('success', 'Order placed successfully!');
        $this->dispatch('cart-updated');
        $this->redirect(route('orders'), navigate: true);
    }

    public function render(): View
    {
        $cartItems = CartItem::query()
            ->with('product')
            ->where('user_id', auth()->id())
            ->get();

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return view('livewire.cart', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }
}
