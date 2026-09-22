<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('jobs:auto-match')
    ->dailyAt('07:00')
    ->withoutOverlapping();

Schedule::command('queue:work --stop-when-empty')
    ->everyTenSeconds()
    // Lock expires after 10 min instead of the 24 h default, so a hard-killed
    // worker (OOM, reboot, SIGKILL) can't block the queue for a day.
    // 10 min > the 330s job timeout, so a healthy run can't overlap itself.
    ->withoutOverlapping(10);
