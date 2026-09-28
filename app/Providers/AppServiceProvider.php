<?php

namespace App\Providers;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EmailCampaignResource::macro(
            'availableActionsFor',
            function (EmailCampaign $campaign): array {
                return match ($campaign->status) {
                    EmailCampaign::STATUS_DRAFT => [
                        'preview',
                        'send_test',
                        'schedule',
                        'send_now',
                        'duplicate',
                    ],

                    EmailCampaign::STATUS_SCHEDULED => [
                        'preview',
                        'cancel_schedule',
                        'duplicate',
                    ],

                    EmailCampaign::STATUS_SENDING => [
                        'pause',
                        'cancel',
                    ],

                    EmailCampaign::STATUS_PAUSED => [
                        'resume',
                        'cancel',
                    ],

                    EmailCampaign::STATUS_COMPLETED,
                    EmailCampaign::STATUS_SENT => [
                        'resend_non_openers',
                        'retry_failed',
                        'duplicate',
                        'export',
                    ],

                    EmailCampaign::STATUS_FAILED => [
                        'retry_failed',
                        'duplicate',
                    ],

                    default => [],
                };
            }
        );
    }
}
