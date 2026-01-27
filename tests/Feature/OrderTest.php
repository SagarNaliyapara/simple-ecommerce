<?php

namespace Tests\Feature;

use App\Livewire\Cart;
use App\Livewire\Orders;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Proceed to Order (from Cart component)
    // ------------------------------------------------------------------
    public function test_proceed_to_order_creates_order_and_order_items(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Orderable Product',
            'price' => 50.00,
            'stock_quantity' => 10,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder')
            ->assertRedirect(route('orders'));

        $this->assertDatabaseHas(Order::class, [
            'user_id' => $user->id,
            'total_amount' => '100.00',
            'status' => 'pending',
        ]);

        $order = Order::query()
            ->where('user_id', $user->id)
            ->first();

        $this->assertDatabaseHas(OrderItem::class, [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => '50.00',
        ]);
    }

    public function test_proceed_to_order_decrements_product_stock(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Stock Product',
            'price' => 30.00,
            'stock_quantity' => 10,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder');

        $this->assertDatabaseHas(Product::class, [
            'id' => $product->id,
            'stock_quantity' => 7,
        ]);
    }

    public function test_proceed_to_order_clears_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Cart Clear Product',
            'price' => 20.00,
            'stock_quantity' => 10,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder');

        $this->assertDatabaseMissing(CartItem::class, [
            'user_id' => $user->id,
        ]);
    }

    public function test_proceed_to_order_dispatches_cart_updated(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Event Product',
            'price' => 15.00,
            'stock_quantity' => 5,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder')
            ->assertDispatched('cart-updated');
    }

    public function test_proceed_to_order_with_empty_cart_shows_error(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(Cart::class);

        $component->call('proceedToOrder');

        $component->assertNoRedirect();

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_proceed_to_order_with_out_of_stock_item_shows_error(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Low Stock Product',
            'price' => 25.00,
            'stock_quantity' => 1,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder')
            ->assertNoRedirect();

        $this->assertDatabaseCount(Order::class, 0);

        $this->assertDatabaseHas(Product::class, [
            'id' => $product->id,
            'stock_quantity' => 1,
        ]);
    }

    public function test_proceed_to_order_with_multiple_products(): void
    {
        $user = User::factory()->create();

        $productA = Product::query()->create([
            'name' => 'Product A',
            'price' => 10.00,
            'stock_quantity' => 20,
        ]);

        $productB = Product::query()->create([
            'name' => 'Product B',
            'price' => 35.00,
            'stock_quantity' => 15,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $productA->id,
            'quantity' => 3,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $productB->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder')
            ->assertRedirect(route('orders'));

        // Total = (3 * 10) + (2 * 35) = 100.00
        $this->assertDatabaseHas(Order::class, [
            'user_id' => $user->id,
            'total_amount' => '100.00',
        ]);

        $order = Order::query()->where('user_id', $user->id)->first();

        $this->assertDatabaseHas(OrderItem::class, [
            'order_id' => $order->id,
            'product_id' => $productA->id,
            'quantity' => 3,
            'price' => '10.00',
        ]);

        $this->assertDatabaseHas(OrderItem::class, [
            'order_id' => $order->id,
            'product_id' => $productB->id,
            'quantity' => 2,
            'price' => '35.00',
        ]);

        $this->assertDatabaseHas(Product::class, [
            'id' => $productA->id,
            'stock_quantity' => 17,
        ]);

        $this->assertDatabaseHas(Product::class, [
            'id' => $productB->id,
            'stock_quantity' => 13,
        ]);

        $this->assertDatabaseMissing(CartItem::class, [
            'user_id' => $user->id,
        ]);
    }

    // ------------------------------------------------------------------
    // Orders page
    // ------------------------------------------------------------------

    public function test_orders_page_requires_authentication(): void
    {
        $response = $this->get(route('orders'));

        $response->assertRedirect('/login');
    }

    public function test_orders_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('orders'))
            ->assertOk();
    }

    public function test_orders_page_shows_empty_state_when_no_orders(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Orders::class)
            ->assertViewHas('orders', fn (Collection $orders) => $orders->isEmpty())
            ->assertSee('No orders yet');
    }

    public function test_orders_page_displays_user_orders(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Ordered Widget',
            'price' => 45.00,
            'stock_quantity' => 10,
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'total_amount' => 90.00,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 45.00,
        ]);

        Livewire::actingAs($user)
            ->test(Orders::class)
            ->assertViewHas('orders', fn (Collection $orders) => $orders->contains($order))
            ->assertSee('Order #'.$order->id)
            ->assertSee('Ordered Widget')
            ->assertSee('$90.00')
            ->assertSee('Pending');
    }

    public function test_orders_page_does_not_show_other_users_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Secret Product',
            'price' => 99.00,
            'stock_quantity' => 10,
        ]);

        $order = Order::query()->create([
            'user_id' => $otherUser->id,
            'total_amount' => 99.00,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 99.00,
        ]);

        Livewire::actingAs($user)
            ->test(Orders::class)
            ->assertViewHas('orders', fn (Collection $orders) => $orders->isEmpty())
            ->assertDontSee('Secret Product')
            ->assertSee('No orders yet');
    }

    public function test_orders_are_displayed_in_descending_order(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Any Product',
            'price' => 10.00,
            'stock_quantity' => 50,
        ]);

        // Create older order in the past
        $this->travel(-1)->days();

        $olderOrder = Order::query()->create([
            'user_id' => $user->id,
            'total_amount' => 10.00,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $olderOrder->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
        ]);

        // Travel back to now for the newer order
        $this->travelBack();

        $newerOrder = Order::query()->create([
            'user_id' => $user->id,
            'total_amount' => 20.00,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $newerOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10.00,
        ]);

        $component = Livewire::actingAs($user)->test(Orders::class);

        $html = $component->html();

        $newerPos = strpos($html, 'Order #'.$newerOrder->id);
        $olderPos = strpos($html, 'Order #'.$olderOrder->id);

        $this->assertNotFalse($newerPos);
        $this->assertNotFalse($olderPos);
        $this->assertTrue($newerPos < $olderPos, 'Newer order should appear before older order');
    }
}
