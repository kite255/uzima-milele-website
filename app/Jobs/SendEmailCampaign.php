<?php

namespace App\Jobs;

use App\Mail\CustomCampaignMail;
use App\Mail\DevotionCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
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

    public function handle(): void
    {
        $campaign = EmailCampaign::query()
            ->with('devotion')
            ->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Only queued/sending campaigns may be processed
        |--------------------------------------------------------------------------
        */
        if (! in_array($campaign->status, [
            EmailCampaign::STATUS_QUEUED,
            EmailCampaign::STATUS_SENDING,
        ], true)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Mark Campaign As Sending
        |--------------------------------------------------------------------------
        */
        if ($campaign->status === EmailCampaign::STATUS_QUEUED) {
            $campaign->update([
                'status' => EmailCampaign::STATUS_SENDING,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Process Pending Recipients
        |--------------------------------------------------------------------------
        */
        EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', EmailCampaignRecipient::STATUS_PENDING)
            ->orderBy('id')
            ->chunkById(
                100,
                function ($recipients) use ($campaign): void {
                    foreach ($recipients as $recipient) {
                        $this->sendToRecipient(
                            $campaign,
                            $recipient
                        );
                    }
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Refresh Final Statistics
        |--------------------------------------------------------------------------
        */
        $sentCount = EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->count();

        $failedCount = EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', EmailCampaignRecipient::STATUS_FAILED)
            ->count();

        $pendingCount = EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', EmailCampaignRecipient::STATUS_PENDING)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Complete Campaign
        |--------------------------------------------------------------------------
        */
        if ($pendingCount === 0) {
            $campaign->update([
                'status' => EmailCampaign::STATUS_SENT,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'sent_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Keep Campaign Sending
        |--------------------------------------------------------------------------
        */
        $campaign->update([
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);
    }

    protected function sendToRecipient(
        EmailCampaign $campaign,
        EmailCampaignRecipient $recipient
    ): void {
        try {
            /*
            |--------------------------------------------------------------------------
            | Devotion Campaign
            |--------------------------------------------------------------------------
            */
            if ($campaign->isDevotion()) {
                Mail::to($recipient->email)
                    ->send(
                        new DevotionCampaignMail(
                            campaign: $campaign,
                            recipient: $recipient,
                        )
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Custom Campaign
            |--------------------------------------------------------------------------
            */
            elseif ($campaign->isCustom()) {
                Mail::to($recipient->email)
                    ->send(
                        new CustomCampaignMail(
                            campaign: $campaign,
                            recipient: $recipient,
                        )
                    );
            } else {
                throw new \RuntimeException(
                    'Unknown email campaign type.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Mark Recipient Sent
            |--------------------------------------------------------------------------
            */
            $recipient->markAsSent();

            $campaign->increment('sent_count');
        } catch (Throwable $exception) {
            report($exception);

            /*
            |--------------------------------------------------------------------------
            | Mark Recipient Failed
            |--------------------------------------------------------------------------
            */
            $recipient->markAsFailed(
                $exception->getMessage()
            );

            $campaign->increment('failed_count');
        }
    }

    /**
     * Called after all queue retry attempts fail.
     */
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