<?php

namespace Tests\Feature\Auth;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('form.name', 'Test User')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password')
            ->set('form.password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_registration_with_pending_cart_product_adds_to_cart(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 29.99,
            'stock_quantity' => 10,
        ]);

        session(['pending_cart_product' => $product->id]);

        $component = Volt::test('pages.auth.register')
            ->set('form.name', 'Test User')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password')
            ->set('form.password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('cart', absolute: false));

        $this->assertAuthenticated();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_registration_with_pending_out_of_stock_product_redirects_to_dashboard(): void
    {
        $product = Product::query()->create([
            'name' => 'Out of Stock Product',
            'price' => 19.99,
            'stock_quantity' => 0,
        ]);

        session(['pending_cart_product' => $product->id]);

        $component = Volt::test('pages.auth.register')
            ->set('form.name', 'Test User')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password')
            ->set('form.password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);
    }
}
