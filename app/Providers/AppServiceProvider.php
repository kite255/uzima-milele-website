<?php

namespace App\Providers;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPublicStorageMirroring();

        EmailCampaignResource::macro(
            'availableActionsFor',
            function (EmailCampaign $campaign): array {
                return match ($campaign->status) {
                    EmailCampaign::STATUS_DRAFT => [
                        'preview', 'send_test', 'schedule', 'send_now', 'duplicate',
                    ],
                    EmailCampaign::STATUS_SCHEDULED => [
                        'preview', 'cancel_schedule', 'duplicate',
                    ],
                    EmailCampaign::STATUS_SENDING => ['pause', 'cancel'],
                    EmailCampaign::STATUS_PAUSED => ['resume', 'cancel'],
                    EmailCampaign::STATUS_COMPLETED,
                    EmailCampaign::STATUS_SENT => [
                        'resend_non_openers', 'retry_failed', 'duplicate', 'export',
                    ],
                    EmailCampaign::STATUS_FAILED => ['retry_failed', 'duplicate'],
                    default => [],
                };
            }
        );
    }

    /**
     * Mirror successful writes to the public disk into cPanel's real
     * web-visible storage directory when PUBLIC_WEB_STORAGE_PATH is set.
     * Laravel remains the source of truth in storage/app/public.
     */
    private function registerPublicStorageMirroring(): void
    {
        FilesystemAdapter::macro('mirrorToPublicWeb', function (string $path): void {
            $destinationRoot = config('filesystems.public_web_root');

            if (! $destinationRoot || ! $this->exists($path)) {
                return;
            }

            $destination = rtrim($destinationRoot, DIRECTORY_SEPARATOR)
                .DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);

            File::ensureDirectoryExists(dirname($destination), 0755, true);

            $stream = $this->readStream($path);
            if ($stream === false) {
                return;
            }

            $target = fopen($destination, 'wb');
            if ($target !== false) {
                stream_copy_to_stream($stream, $target);
                fclose($target);
            }
            fclose($stream);
        });
    }
}
