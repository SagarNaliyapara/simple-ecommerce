<?php

namespace App\Jobs;

use App\Mail\LowStockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendLowStockNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Collection $products,
    ) {}

    public function handle(): void
    {
        $adminEmail = config('mail.admin_email');

        if (! $adminEmail) {
            return;
        }

        Mail::to($adminEmail)->send(new LowStockAlert($this->products));
    }
}
