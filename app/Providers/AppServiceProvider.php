<?php

namespace App\Providers;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use App\Models\LessonEnrollment;
use App\Observers\LessonEnrollmentObserver;
use App\Services\InstructorEnrollmentReferralService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            InstructorEnrollmentReferralService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire validates temporary uploads before the resource-specific rules run.
        // Other upload fields retain their own configured validation limits.
        config(['livewire.temporary_file_upload.rules' => ['required', 'file', 'max:153600']]);

        $this->loadRoutesFrom(
            base_path('routes/instructor-enrollment-links.php')
        );

        LessonEnrollment::observe(
            LessonEnrollmentObserver::class
        );

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
