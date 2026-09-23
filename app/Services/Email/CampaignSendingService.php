<?php

namespace App\Services\Email;

use App\Jobs\SendEmailCampaign;
use App\Mail\CustomCampaignMail;
use App\Mail\DevotionCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class CampaignSendingService
{
    public function __construct(
        protected CampaignSuppressionService $suppressionService
    ) {}

    public function queue(EmailCampaign $campaign): void
    {
        SendEmailCampaign::dispatch($campaign->id);
    }

    public function processBatch(int $campaignId): void
    {
        $lock = Cache::lock("email-campaign:send:{$campaignId}", 300);

        if (! $lock->get()) {
            return;
        }

        try {
            $campaign = EmailCampaign::query()
                ->with('devotion')
                ->find($campaignId);

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
                $this->sendToRecipient($campaign, $recipient);
            }

            $this->refreshCampaignCountsAndScheduleNextBatch(
                $campaign,
                $batchDelayMinutes
            );
        } finally {
            optional($lock)->release();
        }
    }

    public function pause(EmailCampaign $campaign): void
    {
        $campaign = $campaign->fresh();

        if (! $campaign || $campaign->status !== EmailCampaign::STATUS_SENDING) {
            return;
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_PAUSED,
            'paused_at' => now(),
        ]);

        $this->audit($campaign, 'paused');
    }

    public function resume(EmailCampaign $campaign): void
    {
        $campaign = $campaign->fresh();

        if (! $campaign || $campaign->status !== EmailCampaign::STATUS_PAUSED) {
            return;
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_SENDING,
            'paused_at' => null,
        ]);

        $this->audit($campaign, 'resumed');

        $this->queue($campaign);
    }

    public function cancel(EmailCampaign $campaign): void
    {
        $campaign = $campaign->fresh();

        if (! $campaign || ! in_array($campaign->status, [
            EmailCampaign::STATUS_QUEUED,
            EmailCampaign::STATUS_SENDING,
            EmailCampaign::STATUS_PAUSED,
        ], true)) {
            return;
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->audit($campaign, 'cancelled');
    }

    public function createRetryDraft(
        EmailCampaign $campaign,
        ?int $createdBy = null
    ): EmailCampaign {
        $campaign = $campaign->fresh();

        $retry = EmailCampaign::query()->create([
            'name' => $campaign->name.' - Retry Failed',
            'type' => $campaign->type,
            'devotion_id' => $campaign->devotion_id,
            'subject' => $campaign->subject,
            'content' => $campaign->content,
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SELECTED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'parent_campaign_id' => $campaign->id,
            'template_id' => $campaign->template_id,
            'created_by' => $createdBy,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        $subscriberIds = $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_FAILED)
            ->whereNotNull('email_subscriber_id')
            ->pluck('email_subscriber_id')
            ->unique()
            ->values()
            ->all();

        if ($subscriberIds !== []) {
            $eligibleSubscriberIds = EmailSubscriber::query()
                ->whereIn('id', $subscriberIds)
                ->get()
                ->filter(fn (EmailSubscriber $subscriber): bool =>
                    $this->suppressionService->canReceive($subscriber)
                )
                ->pluck('id')
                ->all();

            $retry->targetSubscribers()->sync($eligibleSubscriberIds);
        }

        $this->audit($campaign, 'retry_failed');

        return $retry->fresh();
    }

    protected function sendToRecipient(
        EmailCampaign $campaign,
        EmailCampaignRecipient $recipient
    ): void {
        try {
            if (! $this->recipientCanReceive($recipient)) {
                $recipient->markAsSuppressed(
                    $this->suppressionReason($recipient)
                );

                return;
            }

            if ($campaign->isDevotion()) {
                Mail::to($recipient->email)
                    ->send(new DevotionCampaignMail(
                        campaign: $campaign,
                        recipient: $recipient,
                    ));
            } elseif ($campaign->isCustom()) {
                Mail::to($recipient->email)
                    ->send(new CustomCampaignMail(
                        campaign: $campaign,
                        recipient: $recipient,
                    ));
            } else {
                throw new RuntimeException('Unknown email campaign type.');
            }

            $recipient->markAsSent();
        } catch (Throwable $exception) {
            report($exception);

            $recipient->markAsFailed(
                $exception->getMessage()
            );
        }
    }

    protected function recipientCanReceive(
        EmailCampaignRecipient $recipient
    ): bool {
        $email = strtolower(trim((string) $recipient->email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($this->suppressionService->isSuppressed($email)) {
            return false;
        }

        if ($recipient->email_subscriber_id === null) {
            return true;
        }

        $subscriber = EmailSubscriber::query()
            ->find($recipient->email_subscriber_id);

        if (! $subscriber) {
            return false;
        }

        return $this->suppressionService->canReceive($subscriber);
    }

    protected function suppressionReason(
        EmailCampaignRecipient $recipient
    ): string {
        $email = strtolower(trim((string) $recipient->email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'invalid_email';
        }

        if ($this->suppressionService->isSuppressed($email)) {
            return 'suppressed';
        }

        if ($recipient->email_subscriber_id !== null) {
            $subscriber = EmailSubscriber::query()
                ->find($recipient->email_subscriber_id);

            if (! $subscriber) {
                return 'subscriber_missing';
            }

            if ($subscriber->status !== 'subscribed') {
                return 'unsubscribed';
            }
        }

        return 'ineligible';
    }

    protected function refreshCampaignCountsAndScheduleNextBatch(
        EmailCampaign $campaign,
        int $batchDelayMinutes
    ): void {
        $sentCount = $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->count();

        $failedCount = $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_FAILED)
            ->count();

        $pendingCount = $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_PENDING)
            ->count();

        if ($pendingCount === 0) {
            $campaign->update([
                'status' => EmailCampaign::STATUS_SENT,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'sent_at' => now(),
                'completed_at' => now(),
                'last_batch_sent_at' => now(),
            ]);

            $this->audit($campaign, 'completed');

            return;
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_SENDING,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'last_batch_sent_at' => now(),
        ]);

        SendEmailCampaign::dispatch($campaign->id)
            ->delay(now()->addMinutes($batchDelayMinutes));

        $this->audit($campaign, 'batch_processed');
    }

    protected function audit(
        EmailCampaign $campaign,
        string $action,
        array $metadata = []
    ): void {
        // Intentionally no-op until CampaignAuditService is added in Task 8.
    }
}
