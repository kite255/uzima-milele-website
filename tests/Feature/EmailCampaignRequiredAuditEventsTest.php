<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Services\Email\CampaignCloneService;
use App\Services\Email\CampaignPreviewService;
use App\Services\EmailCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignRequiredAuditEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_creation_is_audited(): void
    {
        $campaign = $this->campaign();

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'created',
        ]);
    }

    public function test_queue_and_schedule_are_audited(): void
    {
        Queue::fake();

        EmailSubscriber::query()->create([
            'name' => 'Eligible Subscriber',
            'email' => 'eligible@example.com',
            'status' => 'subscribed',
        ]);

        $queued = $this->campaign(['name' => 'Queued Audit']);
        app(EmailCampaignService::class)->queueCampaign($queued);

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $queued->id,
            'action' => 'queued',
        ]);

        $scheduled = $this->campaign(['name' => 'Scheduled Audit']);
        app(EmailCampaignService::class)->scheduleCampaign(
            $scheduled,
            now()->addHour()
        );

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $scheduled->id,
            'action' => 'scheduled',
        ]);
    }

    public function test_duplicate_and_resend_to_non_openers_are_audited(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Unopened Subscriber',
            'email' => 'unopened-audit@example.com',
            'status' => 'subscribed',
        ]);

        $source = $this->campaign([
            'name' => 'Original Audit Campaign',
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 1,
            'sent_count' => 1,
            'sent_at' => now(),
        ]);

        EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $source->id,
            'email_subscriber_id' => $subscriber->id,
            'name' => $subscriber->name,
            'email' => $subscriber->email,
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now(),
            'first_opened_at' => null,
        ]);

        $cloneService = app(CampaignCloneService::class);
        $cloneService->duplicate($source);
        $cloneService->resendToNonOpeners($source);

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $source->id,
            'action' => 'duplicated',
        ]);

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $source->id,
            'action' => 'resend_non_openers',
        ]);
    }

    public function test_test_email_send_is_audited(): void
    {
        Mail::fake();

        $campaign = $this->campaign(['name' => 'Test Email Audit']);

        app(CampaignPreviewService::class)->sendTest(
            $campaign,
            ['audit-test@example.com']
        );

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'test_email_sent',
        ]);
    }

    private function campaign(array $overrides = []): EmailCampaign
    {
        return EmailCampaign::query()->create(array_merge([
            'name' => 'Required Audit Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Required Audit',
            'content' => '<p>Audit content</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ], $overrides));
    }
}
