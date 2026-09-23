<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Services\Email\CampaignSendingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignSendingControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_paused_campaign_does_not_process_next_batch(): void
    {
        Mail::fake();
        Queue::fake();

        $campaign = $this->campaignWithPendingRecipients(25, [
            'status' => EmailCampaign::STATUS_PAUSED,
        ]);

        app(CampaignSendingService::class)->processBatch($campaign->id);

        $this->assertSame(25, $campaign->fresh()->pendingRecipients()->count());
        Mail::assertNothingSent();
        Queue::assertNotPushed(SendEmailCampaign::class);
    }

    public function test_send_time_recheck_suppresses_recently_unsubscribed_recipient(): void
    {
        Mail::fake();
        Queue::fake();

        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Recently Unsubscribed',
            'email' => 'recently-unsubscribed@example.com',
            'status' => 'subscribed',
        ]);

        $campaign = $this->campaign([
            'status' => EmailCampaign::STATUS_QUEUED,
            'total_recipients' => 1,
        ]);

        $recipient = $this->recipient($campaign, $subscriber);

        // The snapshot existed while subscribed; consent changes before the worker sends.
        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        app(CampaignSendingService::class)->processBatch($campaign->id);

        $recipient->refresh();

        $this->assertSame(EmailCampaignRecipient::STATUS_SUPPRESSED, $recipient->status);
        $this->assertNotNull($recipient->suppressed_at);
        $this->assertNotNull($recipient->suppression_reason);
        Mail::assertNothingSent();
    }

    public function test_pause_resume_and_cancel_control_campaign_lifecycle(): void
    {
        Queue::fake();

        $service = app(CampaignSendingService::class);

        $campaign = $this->campaignWithPendingRecipients(2, [
            'status' => EmailCampaign::STATUS_SENDING,
        ]);

        $service->pause($campaign);
        $campaign->refresh();

        $this->assertSame(EmailCampaign::STATUS_PAUSED, $campaign->status);
        $this->assertNotNull($campaign->paused_at);

        $service->resume($campaign);
        $campaign->refresh();

        $this->assertSame(EmailCampaign::STATUS_SENDING, $campaign->status);
        $this->assertNull($campaign->paused_at);
        Queue::assertPushed(
            SendEmailCampaign::class,
            fn (SendEmailCampaign $job): bool => $job->campaignId === $campaign->id
        );

        Queue::fake();
        $service->cancel($campaign);
        $campaign->refresh();

        $this->assertSame(EmailCampaign::STATUS_CANCELLED, $campaign->status);
        $this->assertNotNull($campaign->cancelled_at);

        $service->processBatch($campaign->id);

        $this->assertSame(2, $campaign->fresh()->pendingRecipients()->count());
        Queue::assertNothingPushed();
    }

    public function test_retry_failed_creates_new_draft_and_preserves_original_history(): void
    {
        $failedSubscriber = EmailSubscriber::query()->create([
            'name' => 'Failed Subscriber',
            'email' => 'failed@example.com',
            'status' => 'subscribed',
        ]);

        $successfulSubscriber = EmailSubscriber::query()->create([
            'name' => 'Successful Subscriber',
            'email' => 'successful@example.com',
            'status' => 'subscribed',
        ]);

        $campaign = $this->campaign([
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 2,
            'sent_count' => 1,
            'failed_count' => 1,
            'sent_at' => now(),
        ]);

        $failed = $this->recipient($campaign, $failedSubscriber, [
            'status' => EmailCampaignRecipient::STATUS_FAILED,
            'failed_at' => now(),
            'failure_count' => 1,
            'error_message' => 'SMTP failure',
        ]);

        $sent = $this->recipient($campaign, $successfulSubscriber, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $retry = app(CampaignSendingService::class)->createRetryDraft($campaign);

        $this->assertNotSame($campaign->id, $retry->id);
        $this->assertSame(EmailCampaign::STATUS_DRAFT, $retry->status);
        $this->assertSame($campaign->id, $retry->parent_campaign_id);
        $this->assertSame(EmailCampaign::RECIPIENT_SCOPE_SELECTED, $retry->recipient_scope);
        $this->assertTrue($retry->targetSubscribers()->whereKey($failedSubscriber->id)->exists());
        $this->assertFalse($retry->targetSubscribers()->whereKey($successfulSubscriber->id)->exists());

        $this->assertSame(EmailCampaignRecipient::STATUS_FAILED, $failed->fresh()->status);
        $this->assertSame(EmailCampaignRecipient::STATUS_SENT, $sent->fresh()->status);
    }

    public function test_processing_same_campaign_twice_sends_each_recipient_at_most_once(): void
    {
        config()->set('mail.campaign_batch_size', 20);
        Mail::fake();
        Queue::fake();

        $campaign = $this->campaignWithPendingRecipients(3, [
            'status' => EmailCampaign::STATUS_QUEUED,
        ]);

        $service = app(CampaignSendingService::class);

        $service->processBatch($campaign->id);
        $service->processBatch($campaign->id);

        Mail::assertSentCount(3);

        $this->assertSame(
            3,
            EmailCampaignRecipient::query()
                ->where('email_campaign_id', $campaign->id)
                ->where('status', EmailCampaignRecipient::STATUS_SENT)
                ->count()
        );
    }

    private function campaign(array $attributes = []): EmailCampaign
    {
        return EmailCampaign::query()->create(array_merge([
            'name' => 'Sending Controls Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Sending controls',
            'content' => '<p>Hello</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_QUEUED,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'queued_at' => now(),
        ], $attributes));
    }

    private function campaignWithPendingRecipients(int $count, array $attributes = []): EmailCampaign
    {
        $campaign = $this->campaign(array_merge([
            'total_recipients' => $count,
        ], $attributes));

        foreach (range(1, $count) as $index) {
            $subscriber = EmailSubscriber::query()->create([
                'name' => "Recipient {$index}",
                'email' => "control{$campaign->id}-{$index}@example.com",
                'status' => 'subscribed',
            ]);

            $this->recipient($campaign, $subscriber);
        }

        return $campaign;
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
