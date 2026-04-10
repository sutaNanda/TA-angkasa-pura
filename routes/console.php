<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Generate daily maintenance tasks at midnight
Schedule::command('maintenance:generate-daily')
    ->dailyAt('00:01')
    ->timezone('Asia/Jakarta')
    ->description('Generate preventive maintenance tasks for today');

// Purge old maintenance and patrol logs monthly (Retain 3 months of data by default)
Schedule::command('maintenance:purge-logs 3')
    ->monthly()
    ->timezone('Asia/Jakarta')
    ->description('Purge maintenance and patrol records older than 3 months');
