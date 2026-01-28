<?php

namespace App\Livewire;

use App\Services\CartService;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProductsList extends Component
{
    protected CartService $cartService;

    protected ProductService $productService;

    public function boot(CartService $cartService, ProductService $productService): void
    {
        $this->cartService = $cartService;
        $this->productService = $productService;
    }

    public function addToCart($productId): void
    {
        if (! auth()->check()) {
            session(['pending_cart_product' => $productId]);
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $userId = auth()->id();

        $product = $this->productService->findOrFail($productId);

        if ($product->stock_quantity < 1) {
            session()->flash('error', 'Product is out of stock.');

            return;
        }

        $cartItem = $this->cartService->findItemByProduct($userId, $productId);

        if ($cartItem) {
            if ($cartItem->quantity >= $product->stock_quantity) {
                session()->flash('error', 'Cannot add more. Stock limit reached.');
                $this->redirect(route('cart'), navigate: true);

                return;
            }

            $this->cartService->incrementQuantity($cartItem);
            session()->flash('success', 'Cart updated successfully.');
        } else {
            $this->cartService->createItem($userId, $productId);
            session()->flash('success', 'Product added to cart.');
        }

        $this->dispatch('cart-updated');
        $this->redirect(route('cart'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.products-list', [
            'products' => $this->productService->getAll(),
        ]);
    }
}
