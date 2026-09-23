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

        if (! in_array($campaign->status, [
            EmailCampaign::STATUS_QUEUED,
            EmailCampaign::STATUS_SENDING,
        ], true)) {
            return;
        }

        if ($campaign->status === EmailCampaign::STATUS_QUEUED) {
            $campaign->update([
                'status' => EmailCampaign::STATUS_SENDING,
            ]);
        }

        $batchSize = max(
            1,
            (int) config('mail.campaign_batch_size', 20)
        );

        $batchDelayMinutes = max(
            1,
            (int) config('mail.campaign_batch_delay_minutes', 10)
        );

        $recipients = EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', EmailCampaignRecipient::STATUS_PENDING)
            ->orderBy('id')
            ->limit($batchSize)
            ->get();

        foreach ($recipients as $recipient) {
            $this->sendToRecipient(
                $campaign,
                $recipient
            );
        }

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

        if ($pendingCount === 0) {
            $campaign->update([
                'status' => EmailCampaign::STATUS_SENT,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'sent_at' => now(),
            ]);

            return;
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_SENDING,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        self::dispatch($campaign->id)
            ->delay(now()->addMinutes($batchDelayMinutes));
    }

    protected function sendToRecipient(
        EmailCampaign $campaign,
        EmailCampaignRecipient $recipient
    ): void {
        try {
            if ($campaign->isDevotion()) {
                Mail::to($recipient->email)
                    ->send(
                        new DevotionCampaignMail(
                            campaign: $campaign,
                            recipient: $recipient,
                        )
                    );
            } elseif ($campaign->isCustom()) {
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

            $recipient->markAsSent();
            $campaign->increment('sent_count');
        } catch (Throwable $exception) {
            report($exception);

            $recipient->markAsFailed(
                $exception->getMessage()
            );

            $campaign->increment('failed_count');
        }
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
