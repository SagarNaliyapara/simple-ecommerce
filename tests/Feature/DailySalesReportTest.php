<?php

namespace Tests\Feature;

use App\Console\Commands\SendDailySalesReportCommand;
use App\Jobs\SendDailySalesReport;
use App\Mail\DailySalesReport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DailySalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_daily_sales_report_job(): void
    {
        Bus::fake([SendDailySalesReport::class]);

        $this->artisan(SendDailySalesReportCommand::class)
            ->assertSuccessful();

        Bus::assertDispatched(SendDailySalesReport::class, function ($job) {
            return $job->date->isSameDay(Carbon::today());
        });
    }

    public function test_command_accepts_date_option(): void
    {
        Bus::fake([SendDailySalesReport::class]);

        $this->artisan(SendDailySalesReportCommand::class, ['--date' => '2025-06-15'])
            ->assertSuccessful();

        Bus::assertDispatched(SendDailySalesReport::class, function ($job) {
            return $job->date->isSameDay(Carbon::parse('2025-06-15'));
        });
    }

    public function test_job_sends_email_with_todays_orders(): void
    {
        Mail::fake();

        config(['mail.admin_email' => 'admin@example.com']);

        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Report Product',
            'price' => 50.00,
            'stock_quantity' => 10,
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        $job = new SendDailySalesReport(Carbon::today());
        $job->handle();

        Mail::assertSent(DailySalesReport::class, function ($mail) {
            return $mail->hasTo('admin@example.com')
                && $mail->orders->count() === 1;
        });
    }

    public function test_job_filters_orders_by_date(): void
    {
        Mail::fake();

        config(['mail.admin_email' => 'admin@example.com']);

        $user = User::factory()->create();

        $product = Product::query()->create([
            'name' => 'Date Filter Product',
            'price' => 30.00,
            'stock_quantity' => 10,
        ]);

        // Yesterday's order
        $this->travel(-1)->days();

        $yesterdayOrder = Order::query()->create([
            'user_id' => $user->id,
            'total_amount' => 30.00,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $yesterdayOrder->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 30.00,
        ]);

        $this->travelBack();

        // Today's order
        $todayOrder = Order::query()->create([
            'user_id' => $user->id,
            'total_amount' => 60.00,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $todayOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 30.00,
        ]);

        $job = new SendDailySalesReport(Carbon::today());
        $job->handle();

        Mail::assertSent(DailySalesReport::class, function ($mail) use ($todayOrder) {
            return $mail->orders->count() === 1
                && $mail->orders->first()->id === $todayOrder->id;
        });
    }

    public function test_job_does_not_send_email_when_admin_email_missing(): void
    {
        Mail::fake();

        config(['mail.admin_email' => null]);

        $job = new SendDailySalesReport(Carbon::today());
        $job->handle();

        Mail::assertNothingSent();
    }

    public function test_job_sends_email_even_with_no_orders(): void
    {
        Mail::fake();

        config(['mail.admin_email' => 'admin@example.com']);

        $job = new SendDailySalesReport(Carbon::today());
        $job->handle();

        Mail::assertSent(DailySalesReport::class, function ($mail) {
            return $mail->orders->isEmpty();
        });
    }
}
