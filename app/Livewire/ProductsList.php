<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductsList extends Component
{
    public function addToCart($productId)
    {
        if (!auth()->check()) {
            session(['pending_cart_product' => $productId]);
            return redirect()->route('login');
        }

        $product = Product::query()->findOrFail($productId);

        if ($product->stock_quantity < 1) {
            session()->flash('error', 'Product is out of stock.');
            return;
        }

        $cartItem = CartItem::query()
            ->where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            if ($cartItem->quantity >= $product->stock_quantity) {
                session()->flash('error', 'Cannot add more. Stock limit reached.');
                return redirect()->route('cart');
            }

            $cartItem->increment('quantity');
            session()->flash('success', 'Cart updated successfully.');
        } else {
            CartItem::query()->create([
                'user_id' => auth()->id(),
                'product_id' => $productId,
                'quantity' => 1,
            ]);
            session()->flash('success', 'Product added to cart.');
        }

        $this->dispatch('cart-updated');
        return redirect()->route('cart');
    }

    public function render()
    {
        return view('livewire.products-list', [
            'products' => Product::all(),
        ]);
    }
}
