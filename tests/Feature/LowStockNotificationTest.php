<?php

namespace Tests\Feature;

use App\DTOs\LowStockProductDTO;
use App\Jobs\SendLowStockNotification;
use App\Livewire\Cart;
use App\Mail\LowStockAlert;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_job_is_dispatched_when_stock_drops_to_threshold(): void
    {
        Bus::fake([SendLowStockNotification::class]);

        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Low Stock Widget',
            'price' => 25.00,
            'stock_quantity' => 5,
            'low_stock_threshold' => 3,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder');

        // stock_quantity after order: 5 - 2 = 3 (equals threshold)
        Bus::assertDispatched(SendLowStockNotification::class, function ($job) {
            return $job->products->contains(fn ($p) => $p->name === 'Low Stock Widget' && $p->stockQuantity === 3);
        });
    }

    public function test_low_stock_job_is_dispatched_when_stock_drops_below_threshold(): void
    {
        Bus::fake([SendLowStockNotification::class]);

        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Almost Gone',
            'price' => 10.00,
            'stock_quantity' => 4,
            'low_stock_threshold' => 3,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder');

        // stock_quantity after order: 4 - 3 = 1 (below threshold)
        Bus::assertDispatched(SendLowStockNotification::class, function ($job) {
            return $job->products->contains(fn ($p) => $p->name === 'Almost Gone' && $p->stockQuantity === 1);
        });
    }

    public function test_low_stock_job_is_not_dispatched_when_stock_stays_above_threshold(): void
    {
        Bus::fake([SendLowStockNotification::class]);

        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Plenty In Stock',
            'price' => 15.00,
            'stock_quantity' => 20,
            'low_stock_threshold' => 3,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder');

        // stock_quantity after order: 20 - 2 = 18 (above threshold)
        Bus::assertNotDispatched(SendLowStockNotification::class);
    }

    public function test_low_stock_job_includes_multiple_low_stock_products(): void
    {
        Bus::fake([SendLowStockNotification::class]);

        $user = User::factory()->create();

        $productA = Product::query()->create([
            'name' => 'Widget A',
            'price' => 10.00,
            'stock_quantity' => 4,
            'low_stock_threshold' => 3,
        ]);

        $productB = Product::query()->create([
            'name' => 'Widget B',
            'price' => 20.00,
            'stock_quantity' => 5,
            'low_stock_threshold' => 3,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $productA->id,
            'quantity' => 2,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $productB->id,
            'quantity' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(Cart::class)
            ->call('proceedToOrder');

        // A: 4-2=2 (low), B: 5-3=2 (low)
        Bus::assertDispatched(SendLowStockNotification::class, function ($job) {
            return $job->products->count() === 2
                && $job->products->contains(fn ($p) => $p->name === 'Widget A')
                && $job->products->contains(fn ($p) => $p->name === 'Widget B');
        });
    }

    public function test_low_stock_job_sends_email_to_admin(): void
    {
        Mail::fake();

        config(['mail.admin_email' => 'admin@example.com']);

        $products = collect([
            new LowStockProductDTO(name: 'Low Item', stockQuantity: 2),
        ]);

        $job = new SendLowStockNotification($products);
        $job->handle();

        Mail::assertSent(LowStockAlert::class, function ($mail) {
            return $mail->hasTo('admin@example.com');
        });
    }

    public function test_low_stock_job_does_not_send_email_when_admin_email_missing(): void
    {
        Mail::fake();

        config(['mail.admin_email' => null]);

        $products = collect([
            new LowStockProductDTO(name: 'Low Item', stockQuantity: 2),
        ]);

        $job = new SendLowStockNotification($products);
        $job->handle();

        Mail::assertNothingSent();
    }
}
