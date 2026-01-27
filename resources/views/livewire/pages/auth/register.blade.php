<?php

use App\Livewire\Forms\RegisterForm;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')]
class extends Component {

    public RegisterForm $form;

    public function register(): void
    {
        $validated = $this->form->validate();

        event(new Registered($user = User::query()->create($validated)));

        Auth::login($user);

        if (session()->has('pending_cart_product')) {
            $productId = session('pending_cart_product');
            session()->forget('pending_cart_product');

            $product = Product::query()->find($productId);

            if ($product && $product->stock_quantity > 0) {
                CartItem::query()
                    ->create([
                        'user_id' => $user->id,
                        'product_id' => $productId,
                        'quantity' => 1,
                    ]);

                session()->flash('success', 'Product added to cart.');

                $this->redirect(route('cart', absolute: false), navigate: true);
                return;
            }
        }

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <div>
            <x-input-label for="name" :value="__('Name')"/>
            <x-text-input
                wire:model="form.name"
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                required
                autofocus autocomplete="name"
            />
            <x-input-error :messages="$errors->get('form.name')" class="mt-2"/>
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')"/>
            <x-text-input
                wire:model="form.email"
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                required
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2"/>
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')"/>

            <x-text-input
                wire:model="form.password"
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2"/>
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input
                wire:model="form.password_confirmation"
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('form.password_confirmation')" class="mt-2"/>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}"
                wire:navigate
            >
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
