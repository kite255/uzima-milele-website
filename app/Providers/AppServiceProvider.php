<?php

namespace App\Providers;

use App\Filament\Resources\EmailCampaignResource;
use App\Http\Controllers\EmailCampaignClickController;
use App\Models\EmailCampaign;
use Illuminate\Support\Facades\Route;
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
        Route::get(
            '/email/click/{token}',
            EmailCampaignClickController::class
        )->name('email-campaigns.click');

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
