<?php

namespace App\Livewire;

use App\Jobs\SendLowStockNotification;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Cart extends Component
{
    protected CartService $cartService;

    public function __construct()
    {
        $this->cartService = app(CartService::class);
    }

    public function updateQuantity($cartItemId, $action): void
    {
        $cartItem = $this->cartService->findItemOrFail($cartItemId, auth()->id());

        $product = $cartItem->product;

        if ($action === 'increment') {
            if ($cartItem->quantity >= $product->stock_quantity) {
                session()->flash('error', 'Cannot add more. Stock limit reached.');

                return;
            }
            $this->cartService->incrementQuantity($cartItem);
            session()->flash('success', 'Quantity updated.');
        } elseif ($action === 'decrement') {
            if ($cartItem->quantity > 1) {
                $this->cartService->decrementQuantity($cartItem);
                session()->flash('success', 'Quantity updated.');
            } else {
                $this->cartService->deleteItem($cartItem);
                session()->flash('success', 'Item removed from cart.');
            }
        }

        $this->dispatch('cart-updated');
    }

    public function removeItem($cartItemId): void
    {
        $this->cartService->removeItem($cartItemId, auth()->id());

        session()->flash('success', 'Item removed from cart.');
        $this->dispatch('cart-updated');
    }

    public function proceedToOrder(): void
    {
        $userId = auth()->id();

        $cartItems = $this->cartService->getItemsWithLock($userId);

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

        $lowStockProducts = app(OrderService::class)->placeOrder($cartItems, $userId);

        if ($lowStockProducts->isNotEmpty()) {
            SendLowStockNotification::dispatch($lowStockProducts);
        }

        session()->flash('success', 'Order placed successfully!');
        $this->dispatch('cart-updated');
        $this->redirect(route('orders'), navigate: true);
    }

    public function render(): View
    {
        $cartItems = $this->cartService->getItems(auth()->id());

        $total = $cartItems->sum(fn ($item) => $item->quantity * $item->productPrice);

        return view('livewire.cart', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }
}
