<?php

namespace Tests\Feature;

use App\Livewire\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_page_requires_authentication(): void
    {
        $this->get(route('cart'))->assertRedirect('/login');
    }

    public function test_cart_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cart'))->assertOk();
    }

    public function test_cart_displays_user_items(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Visible Product',
            'price' => $price = 42.50,
            'stock_quantity' => 10,
        ]);

        $cartItem = CartItem::query()
            ->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->assertViewHas('cartItems', fn (Collection $cartItems) => $cartItems->contains(fn ($item) => $item->id === $cartItem->id))
            ->assertViewHas('total', ($price * $cartItem->quantity))
            ->assertSee('Visible Product')
            ->assertSee('$42.50');
    }

    public function test_cart_does_not_display_other_users_items(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Other User Product',
            'price' => 30.00,
            'stock_quantity' => 10,
        ]);

        CartItem::query()->create([
            'user_id' => $otherUser->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->assertViewHas('cartItems', fn (Collection $cartItems) => $cartItems->isEmpty())
            ->assertViewHas('total', null)
            ->assertDontSee('Other User Product');
    }

    public function test_empty_cart_shows_empty_message(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->assertViewHas('cartItems', fn (Collection $cartItems) => $cartItems->isEmpty())
            ->assertViewHas('total', null)
            ->assertSee('Your cart is empty');
    }

    public function test_increment_cart_item_quantity(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'stock_quantity' => 10,
        ]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('updateQuantity', $cartItem->id, 'increment')
            ->assertSee('Quantity updated.')
            ->assertDispatched('cart-updated');

        $this->assertDatabaseHas(CartItem::class, [
            'id' => $cartItem->id,
            'quantity' => 3,
        ]);
    }

    public function test_increment_beyond_stock_limit_shows_error(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Limited Product',
            'price' => 20.00,
            'stock_quantity' => 3,
        ]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->assertDontSee('Cannot add more. Stock limit reached.')
            ->call('updateQuantity', $cartItem->id, 'increment')
            ->assertSee('Cannot add more. Stock limit reached.')
            ->assertNotDispatched('cart-updated');

        $this->assertDatabaseHas(CartItem::class, [
            'id' => $cartItem->id,
            'quantity' => 3,
        ]);
    }

    public function test_decrement_cart_item_quantity(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'stock_quantity' => 10,
        ]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('updateQuantity', $cartItem->id, 'decrement')
            ->assertSee('Quantity updated.')
            ->assertDispatched('cart-updated');

        $this->assertDatabaseHas(CartItem::class, [
            'id' => $cartItem->id,
            'quantity' => 2,
        ]);
    }

    public function test_decrement_quantity_one_removes_item(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'stock_quantity' => 10,
        ]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('updateQuantity', $cartItem->id, 'decrement')
            ->assertDontSee('Quantity updated.')
            ->assertSee('Item removed from cart.')
            ->assertDispatched('cart-updated');

        $this->assertDatabaseMissing(CartItem::class, [
            'id' => $cartItem->id,
        ]);
    }

    public function test_remove_item_from_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'stock_quantity' => 10,
        ]);

        $cartItem = CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('removeItem', $cartItem->id)
            ->assertSee('Item removed from cart.')
            ->assertDispatched('cart-updated');

        $this->assertDatabaseMissing(CartItem::class, [
            'id' => $cartItem->id,
        ]);
    }

    public function test_user_cannot_update_another_users_cart_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'stock_quantity' => 10,
        ]);

        $cartItem = CartItem::query()->create([
            'user_id' => $otherUser->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('updateQuantity', $cartItem->id, 'increment')
            ->assertNotDispatched('cart-updated');
    }

    public function test_user_cannot_remove_another_users_cart_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'stock_quantity' => 10,
        ]);

        $cartItem = CartItem::query()->create([
            'user_id' => $otherUser->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('removeItem', $cartItem->id);

        $this->assertDatabaseHas(CartItem::class, [
            'id' => $cartItem->id,
            'quantity' => 2,
        ]);
    }

    public function test_cart_displays_correct_total(): void
    {
        $user = User::factory()->create();

        $productA = Product::query()->create([
            'name' => 'Product A',
            'price' => 10.00,
            'stock_quantity' => 10,
        ]);

        $productB = Product::query()->create([
            'name' => 'Product B',
            'price' => 25.50,
            'stock_quantity' => 10,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $productA->id,
            'quantity' => 2,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $productB->id,
            'quantity' => 1,
        ]);

        // Total = (2 * 10.00) + (1 * 25.50) = 45.50
        Livewire::actingAs($user)
            ->test(Cart::class)
            ->assertViewHas('total', 45.50)
            ->assertSee('$45.50');
    }
}
