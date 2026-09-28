<?php

use App\Services\EmailCampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Public storage synchronization
|--------------------------------------------------------------------------
| cPanel installations may have public_html outside Laravel's application
| directory and may not permit the normal public/storage symbolic link.
| This command safely mirrors storage/app/public into that web directory.
*/
Artisan::command('storage:sync-public {--dry-run : Validate configuration without copying files}', function () {
    $source = storage_path('app/public');
    $destination = config('filesystems.public_web_root');

    if (! $destination) {
        $this->warn('PUBLIC_WEB_STORAGE_PATH is not configured; nothing to synchronize.');
        return self::SUCCESS;
    }

    if (! is_dir($source)) {
        $this->error("Public storage source does not exist: {$source}");
        return self::FAILURE;
    }

    if ($this->option('dry-run')) {
        $this->info("Source: {$source}");
        $this->info("Destination: {$destination}");
        return self::SUCCESS;
    }

    File::ensureDirectoryExists($destination, 0755, true);
    File::copyDirectory($source, $destination);

    $this->info("Public storage synchronized to {$destination}");
    return self::SUCCESS;
})->purpose('Mirror Laravel public storage to the web-visible storage directory');

/* Automatic Lesson Reminders */
Schedule::command('lessons:send-automatic-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

/* Scheduled Email Campaigns */
Schedule::call(function (): void {
    app(EmailCampaignService::class)->releaseDueCampaigns();
})
    ->name('release-due-email-campaigns')
    ->everyMinute()
    ->withoutOverlapping();
