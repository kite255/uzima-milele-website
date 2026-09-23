<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignCloneService
{
    public function __construct(
        protected CampaignSuppressionService $suppressionService,
        protected CampaignSendingService $sendingService
    ) {}

    public function duplicate(
        EmailCampaign $source,
        ?int $createdBy = null
    ): EmailCampaign {
        return DB::transaction(function () use ($source, $createdBy): EmailCampaign {
            $source = EmailCampaign::query()->findOrFail($source->getKey());

            $duplicate = EmailCampaign::query()->create([
                'name' => $source->name.' Copy',
                'type' => $source->type,
                'devotion_id' => $source->devotion_id,
                'subject' => $source->subject,
                'content' => $source->content,
                'recipient_scope' => $source->recipient_scope,
                'email_subscriber_group_id' => $source->email_subscriber_group_id,
                'status' => EmailCampaign::STATUS_DRAFT,
                'total_recipients' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'scheduled_at' => null,
                'queued_at' => null,
                'sent_at' => null,
                'paused_at' => null,
                'cancelled_at' => null,
                'completed_at' => null,
                'parent_campaign_id' => $source->id,
                'audience_filter_type' => $source->audience_filter_type,
                'audience_filter_value' => $source->audience_filter_value,
                'template_id' => $source->template_id,
                'last_batch_sent_at' => null,
                'created_by' => $createdBy,
            ]);

            if ($source->sendsToSelectedSubscribers()) {
                $duplicate->targetSubscribers()->sync(
                    $source->targetSubscribers()->pluck('email_subscribers.id')->all()
                );
            }

            return $duplicate->fresh();
        });
    }

    public function resendToNonOpeners(
        EmailCampaign $source,
        ?int $createdBy = null
    ): EmailCampaign {
        return DB::transaction(function () use ($source, $createdBy): EmailCampaign {
            $source = EmailCampaign::query()->findOrFail($source->getKey());

            $subscriberIds = EmailCampaignRecipient::query()
                ->where('email_campaign_id', $source->id)
                ->where('status', EmailCampaignRecipient::STATUS_SENT)
                ->whereNull('first_opened_at')
                ->whereNotNull('email_subscriber_id')
                ->pluck('email_subscriber_id')
                ->unique()
                ->values();

            $eligibleIds = EmailSubscriber::query()
                ->whereIn('id', $subscriberIds)
                ->get()
                ->filter(fn (EmailSubscriber $subscriber): bool =>
                    $this->suppressionService->canReceive($subscriber)
                )
                ->pluck('id')
                ->values()
                ->all();

            if ($eligibleIds === []) {
                throw ValidationException::withMessages([
                    'recipients' => 'Hakuna wapokeaji ambao hawajafungua kampeni hii na bado wanaruhusiwa kupokea barua pepe.',
                ]);
            }

            $resend = $this->selectedDraftFromSource(
                $source,
                $createdBy,
                'Resend to non-openers'
            );

            $resend->targetSubscribers()->sync($eligibleIds);

            return $resend->fresh();
        });
    }

    public function retryFailed(
        EmailCampaign $source,
        ?int $createdBy = null
    ): EmailCampaign {
        return $this->sendingService->createRetryDraft($source, $createdBy);
    }

    protected function selectedDraftFromSource(
        EmailCampaign $source,
        ?int $createdBy,
        string $suffix
    ): EmailCampaign {
        return EmailCampaign::query()->create([
            'name' => $source->name.' - '.$suffix,
            'type' => $source->type,
            'devotion_id' => $source->devotion_id,
            'subject' => $source->subject,
            'content' => $source->content,
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SELECTED,
            'email_subscriber_group_id' => null,
            'status' => EmailCampaign::STATUS_DRAFT,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'scheduled_at' => null,
            'queued_at' => null,
            'sent_at' => null,
            'paused_at' => null,
            'cancelled_at' => null,
            'completed_at' => null,
            'parent_campaign_id' => $source->id,
            'audience_filter_type' => null,
            'audience_filter_value' => null,
            'template_id' => $source->template_id,
            'last_batch_sent_at' => null,
            'created_by' => $createdBy,
        ]);
    }
}
