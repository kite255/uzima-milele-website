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
| Laravel storage/app/public remains the source of truth. Copy individual
| files into the web-visible directory instead of recursively mirroring the
| whole tree, which is unreliable on some jailed cPanel filesystems.
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

    try {
        if (! is_dir($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $copied = 0;
        $skipped = 0;

        foreach (File::allFiles($source) as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());

            // Livewire temporary uploads must never be published.
            if ($relativePath === 'livewire-tmp' || str_starts_with($relativePath, 'livewire-tmp/')) {
                $skipped++;
                continue;
            }

            $target = rtrim($destination, DIRECTORY_SEPARATOR)
                .DIRECTORY_SEPARATOR
                .str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $targetDirectory = dirname($target);

            if (! is_dir($targetDirectory)) {
                File::makeDirectory($targetDirectory, 0755, true);
            }

            $shouldCopy = ! is_file($target)
                || $file->getSize() !== filesize($target)
                || $file->getMTime() > filemtime($target);

            if (! $shouldCopy) {
                $skipped++;
                continue;
            }

            if (! File::copy($file->getPathname(), $target)) {
                throw new RuntimeException("Unable to copy {$relativePath} to {$target}");
            }

            $copied++;
        }

        $this->info("Public storage synchronized to {$destination} ({$copied} copied, {$skipped} skipped).");
        return self::SUCCESS;
    } catch (Throwable $exception) {
        $this->error('Public storage synchronization failed: '.$exception->getMessage());
        return self::FAILURE;
    }
})->purpose('Copy Laravel public files to the web-visible storage directory');

// Keep cPanel's web-visible storage current without relying on symlinks.
Schedule::command('storage:sync-public')
    ->everyMinute()
    ->withoutOverlapping();

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
