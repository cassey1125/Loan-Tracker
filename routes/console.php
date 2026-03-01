<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('loans:reconcile-statuses')->dailyAt('01:00');
Schedule::command('financial:monitor')->dailyAt('01:30');
Schedule::command('db:backup-daily')->dailyAt('02:00');
Schedule::command('db:backup-verify')->dailyAt('03:00');
Schedule::command('financial:reconcile')->monthlyOn(1, '04:00');
