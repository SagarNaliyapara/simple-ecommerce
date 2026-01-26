<?php

use App\Livewire\Forms\LoginForm;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();
        
        // Check if there's a pending product to add to cart
        if (session()->has('pending_cart_product')) {
            $productId = session('pending_cart_product');
            session()->forget('pending_cart_product');
            
            // Add product to cart
            $product = Product::find($productId);
            
            if ($product && $product->stock_quantity > 0) {
                $cartItem = CartItem::where('user_id', auth()->id())
                    ->where('product_id', $productId)
                    ->first();
                
                if ($cartItem) {
                    if ($cartItem->quantity < $product->stock_quantity) {
                        $cartItem->increment('quantity');
                        session()->flash('success', 'Product added to cart.');
                    }
                } else {
                    CartItem::create([
                        'user_id' => auth()->id(),
                        'product_id' => $productId,
                        'quantity' => 1,
                    ]);
                    session()->flash('success', 'Product added to cart.');
                }
                
                // Redirect to cart page
                $this->redirect(route('cart', absolute: false), navigate: true);
                return;
            }
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>
