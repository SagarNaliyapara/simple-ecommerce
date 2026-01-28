<?php

namespace App\Console\Commands;

use App\Jobs\SendDailySalesReport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailySalesReportCommand extends Command
{
    protected $signature = 'report:daily-sales {--date= : The date to report on (Y-m-d), defaults to today}';

    protected $description = 'Dispatch a job to email the daily sales report';

    public function handle(): int
    {
        $dateString = $this->option('date');

        $date = $dateString
            ? Carbon::createFromFormat('Y-m-d', $dateString)->startOfDay()
            : Carbon::today();

        SendDailySalesReport::dispatch($date);

        $this->info("Daily sales report job dispatched for {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
