<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductsList extends Component
{
    public function addToCart($productId)
    {
        if (!auth()->check()) {
            session(['pending_cart_product' => $productId]);
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $userId = auth()->id();

        $product = Product::query()->findOrFail($productId);

        if ($product->stock_quantity < 1) {
            session()->flash('error', 'Product is out of stock.');
            return;
        }

        $cartItem = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            if ($cartItem->quantity >= $product->stock_quantity) {
                session()->flash('error', 'Cannot add more. Stock limit reached.');
                $this->redirect(route('cart'), navigate: true);
                return;
            }

            $cartItem->increment('quantity');
            session()->flash('success', 'Cart updated successfully.');
        } else {
            CartItem::query()->create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => 1,
            ]);
            session()->flash('success', 'Product added to cart.');
        }

        $this->dispatch('cart-updated');
        $this->redirect(route('cart'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.products-list', [
            'products' => Product::all(),
        ]);
    }
}
