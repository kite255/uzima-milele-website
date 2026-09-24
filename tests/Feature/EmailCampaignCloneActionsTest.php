<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Models\EmailSuppression;
use App\Services\Email\CampaignCloneService;
use App\Services\Email\CampaignSuppressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignCloneActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_copies_configuration_but_not_history_or_recipients(): void
    {
        $source = EmailCampaign::query()->create([
            'name' => 'Original Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Original Subject',
            'content' => '<p>Original Content</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 10,
            'sent_count' => 9,
            'failed_count' => 1,
            'scheduled_at' => now()->subDay(),
            'queued_at' => now()->subDay(),
            'sent_at' => now()->subHours(20),
            'audience_filter_type' => 'new_30_days',
            'audience_filter_value' => null,
        ]);

        EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $source->id,
            'name' => 'Original Recipient',
            'email' => 'original@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subHours(20),
        ]);

        $duplicate = app(CampaignCloneService::class)->duplicate($source);

        $this->assertNotSame($source->id, $duplicate->id);
        $this->assertSame(EmailCampaign::STATUS_DRAFT, $duplicate->status);
        $this->assertSame($source->subject, $duplicate->subject);
        $this->assertSame($source->content, $duplicate->content);
        $this->assertSame($source->recipient_scope, $duplicate->recipient_scope);
        $this->assertSame($source->audience_filter_type, $duplicate->audience_filter_type);
        $this->assertSame(0, $duplicate->total_recipients);
        $this->assertSame(0, $duplicate->sent_count);
        $this->assertSame(0, $duplicate->failed_count);
        $this->assertNull($duplicate->scheduled_at);
        $this->assertNull($duplicate->queued_at);
        $this->assertNull($duplicate->sent_at);
        $this->assertSame(0, $duplicate->recipients()->count());
    }

    public function test_resend_to_non_openers_creates_new_draft_with_only_eligible_unopened_sent_subscribers(): void
    {
        $opened = EmailSubscriber::query()->create([
            'name' => 'Opened',
            'email' => 'opened@example.com',
            'status' => 'subscribed',
        ]);

        $unopened = EmailSubscriber::query()->create([
            'name' => 'Unopened',
            'email' => 'unopened@example.com',
            'status' => 'subscribed',
        ]);

        $suppressed = EmailSubscriber::query()->create([
            'name' => 'Suppressed',
            'email' => 'suppressed-nonopener@example.com',
            'status' => 'subscribed',
        ]);

        $failed = EmailSubscriber::query()->create([
            'name' => 'Failed',
            'email' => 'failed-nonopener@example.com',
            'status' => 'subscribed',
        ]);

        $source = $this->sentCampaign();

        $this->recipient($source, $opened, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subHour(),
            'first_opened_at' => now()->subMinutes(40),
        ]);

        $this->recipient($source, $unopened, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subHour(),
            'first_opened_at' => null,
        ]);

        $this->recipient($source, $suppressed, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subHour(),
            'first_opened_at' => null,
        ]);

        $this->recipient($source, $failed, [
            'status' => EmailCampaignRecipient::STATUS_FAILED,
            'failed_at' => now()->subHour(),
        ]);

        app(CampaignSuppressionService::class)->suppress(
            $suppressed->email,
            EmailSuppression::REASON_MANUAL,
            'admin'
        );

        $resend = app(CampaignCloneService::class)->resendToNonOpeners($source);

        $this->assertSame(EmailCampaign::STATUS_DRAFT, $resend->status);
        $this->assertSame($source->id, $resend->parent_campaign_id);
        $this->assertSame(EmailCampaign::RECIPIENT_SCOPE_SELECTED, $resend->recipient_scope);
        $this->assertTrue($resend->targetSubscribers()->whereKey($unopened->id)->exists());
        $this->assertFalse($resend->targetSubscribers()->whereKey($opened->id)->exists());
        $this->assertFalse($resend->targetSubscribers()->whereKey($suppressed->id)->exists());
        $this->assertFalse($resend->targetSubscribers()->whereKey($failed->id)->exists());
        $this->assertSame(0, $resend->recipients()->count());
    }

    public function test_retry_failed_creates_new_draft_without_mutating_original_recipients(): void
    {
        $failedSubscriber = EmailSubscriber::query()->create([
            'name' => 'Failed Subscriber',
            'email' => 'retry-failed@example.com',
            'status' => 'subscribed',
        ]);

        $sentSubscriber = EmailSubscriber::query()->create([
            'name' => 'Sent Subscriber',
            'email' => 'retry-sent@example.com',
            'status' => 'subscribed',
        ]);

        $source = $this->sentCampaign();

        $failedRecipient = $this->recipient($source, $failedSubscriber, [
            'status' => EmailCampaignRecipient::STATUS_FAILED,
            'failed_at' => now(),
            'failure_count' => 1,
        ]);

        $sentRecipient = $this->recipient($source, $sentSubscriber, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $retry = app(CampaignCloneService::class)->retryFailed($source);

        $this->assertSame(EmailCampaign::STATUS_DRAFT, $retry->status);
        $this->assertSame($source->id, $retry->parent_campaign_id);
        $this->assertSame(EmailCampaign::RECIPIENT_SCOPE_SELECTED, $retry->recipient_scope);
        $this->assertTrue($retry->targetSubscribers()->whereKey($failedSubscriber->id)->exists());
        $this->assertFalse($retry->targetSubscribers()->whereKey($sentSubscriber->id)->exists());
        $this->assertSame(EmailCampaignRecipient::STATUS_FAILED, $failedRecipient->fresh()->status);
        $this->assertSame(EmailCampaignRecipient::STATUS_SENT, $sentRecipient->fresh()->status);
    }

    private function sentCampaign(): EmailCampaign
    {
        return EmailCampaign::query()->create([
            'name' => 'Sent Source',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Source Subject',
            'content' => '<p>Source Content</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 4,
            'sent_count' => 3,
            'failed_count' => 1,
            'sent_at' => now()->subHour(),
        ]);
    }

    private function recipient(
        EmailCampaign $campaign,
        EmailSubscriber $subscriber,
        array $attributes = []
    ): EmailCampaignRecipient {
        return EmailCampaignRecipient::query()->create(array_merge([
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $subscriber->id,
            'name' => $subscriber->name,
            'email' => $subscriber->email,
            'status' => EmailCampaignRecipient::STATUS_PENDING,
        ], $attributes));
    }
}
