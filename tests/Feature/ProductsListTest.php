<?php

namespace Tests\Feature;

use App\Livewire\ProductsList;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_page_can_be_rendered_for_guests(): void
    {
        $this->get(route('products'))->assertOk();
    }

    public function test_products_page_can_be_rendered_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('products'))
            ->assertOk();
    }

    public function test_products_are_displayed_on_the_page(): void
    {
        $product = Product::query()->create([
            'name' => 'Widget Alpha',
            'price' => 15.00,
            'stock_quantity' => 5,
        ]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ProductsList::class)
            ->assertViewHas('products', fn (Collection $products) => $products->contains($product))
            ->assertSee('Widget Alpha')
            ->assertSee('$15.00');
    }

    public function test_guest_add_to_cart_stores_pending_product_and_redirects_to_login(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'stock_quantity' => 5,
        ]);

        Livewire::test(ProductsList::class)
            ->call('addToCart', $product->id)
            ->assertRedirect(route('login'));

        $this->assertEquals($product->id, session('pending_cart_product'));
    }

    public function test_authenticated_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 25.00,
            'stock_quantity' => 10,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsList::class)
            ->call('addToCart', $product->id)
            ->assertRedirect(route('cart'));

        $this->assertDatabaseHas(CartItem::class, [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_adding_existing_cart_product_increments_quantity(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 25.00,
            'stock_quantity' => 10,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsList::class)
            ->call('addToCart', $product->id)
            ->assertRedirect(route('cart'));

        $this->assertDatabaseHas(CartItem::class, [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_adding_out_of_stock_product_shows_error(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Out of Stock',
            'price' => 25.00,
            'stock_quantity' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsList::class)
            ->call('addToCart', $product->id)
            ->assertNoRedirect();

        $this->assertDatabaseMissing(CartItem::class, [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_adding_product_at_stock_limit_shows_error_and_redirects_to_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Limited Stock',
            'price' => 25.00,
            'stock_quantity' => 3,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsList::class)
            ->call('addToCart', $product->id)
            ->assertRedirect(route('cart'));

        $this->assertDatabaseHas(CartItem::class, [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_add_to_cart_dispatches_cart_updated_event(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 25.00,
            'stock_quantity' => 10,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsList::class)
            ->call('addToCart', $product->id)
            ->assertDispatched('cart-updated');
    }
}
