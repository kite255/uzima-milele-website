<?php

use App\Services\EmailCampaignService;
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
| Runs daily at 09:00.
| Sends Email + Dashboard notification only.
| SMS is manual only from Filament admin.
*/
Schedule::command('lessons:send-automatic-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Scheduled Email Campaigns
|--------------------------------------------------------------------------
| Checks every minute for email campaigns whose scheduled time
| has arrived, then moves them into the normal email queue.
|
| Actual delivery is still handled by SendEmailCampaign jobs.
*/
Schedule::call(function (): void {
    app(EmailCampaignService::class)
        ->releaseDueCampaigns();
})
    ->name('release-due-email-campaigns')
    ->everyMinute()
    ->withoutOverlapping();