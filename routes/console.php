<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automatic Lesson Reminders
|--------------------------------------------------------------------------
| Runs daily at 08:00.
| Sends Email + Dashboard notification only.
| SMS is manual only from Filament admin.
*/
Schedule::command('lessons:send-automatic-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();