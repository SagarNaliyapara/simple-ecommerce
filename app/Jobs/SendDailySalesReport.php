<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Carbon $date,
    ) {}

    public function handle(): void
    {
        $adminEmail = config('mail.admin_email');

        if (! $adminEmail) {
            return;
        }

        $orders = Order::query()
            ->with('orderItems.product')
            ->whereDate('created_at', $this->date)
            ->get();

        Mail::to($adminEmail)->send(new DailySalesReport($this->date, $orders));
    }
}
