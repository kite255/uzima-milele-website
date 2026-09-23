<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Services\Email\CampaignSendingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendEmailCampaign implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $campaignId
    ) {
    }

    public function handle(CampaignSendingService $service): void
    {
        $service->processBatch($this->campaignId);
    }

    public function failed(?Throwable $exception): void
    {
        $campaign = EmailCampaign::query()
            ->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_FAILED,
        ]);
    }
}
