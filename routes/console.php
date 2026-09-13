<?php

use App\Services\EmailCampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(
        Inspiring::quote()
    );
})->purpose(
    'Display an inspiring quote'
);

/*
|--------------------------------------------------------------------------
| Automatic Lesson Reminders
|--------------------------------------------------------------------------
| Runs daily at 09:00.
| Sends Email + Dashboard notification only.
| SMS is manual only from Filament admin.
*/
Schedule::command(
    'lessons:send-automatic-reminders'
)
    ->dailyAt('09:00')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Scheduled Email Campaigns
|--------------------------------------------------------------------------
| Checks every minute for scheduled email campaigns whose sending time
| has arrived.
|
| The service moves eligible campaigns into the normal sending queue.
| Actual email delivery is handled by the SendEmailCampaign job.
*/
Schedule::call(function (): void {
    app(
        EmailCampaignService::class
    )->releaseDueCampaigns();
})
    ->name(
        'release-due-email-campaigns'
    )
    ->everyMinute()
    ->withoutOverlapping();