<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignSendingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignLifecycleAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_pause_resume_and_cancel_are_audited(): void
    {
        Queue::fake();

        $campaign = $this->campaign([
            'status' => EmailCampaign::STATUS_SENDING,
        ]);

        $service = app(CampaignSendingService::class);

        $service->pause($campaign);
        $service->resume($campaign->fresh());
        $service->cancel($campaign->fresh());

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'paused',
        ]);
        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'resumed',
        ]);
        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'cancelled',
        ]);
    }

    public function test_processing_batch_records_started_and_completed(): void
    {
        Mail::fake();
        Queue::fake();

        $campaign = $this->campaign([
            'status' => EmailCampaign::STATUS_QUEUED,
            'total_recipients' => 1,
        ]);

        EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Audit Recipient',
            'email' => 'audit-recipient@example.com',
            'status' => EmailCampaignRecipient::STATUS_PENDING,
        ]);

        app(CampaignSendingService::class)->processBatch($campaign->id);

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'started',
        ]);
        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'completed',
        ]);
    }

    private function campaign(array $overrides = []): EmailCampaign
    {
        return EmailCampaign::query()->create(array_merge([
            'name' => 'Lifecycle Audit Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Audit Lifecycle',
            'content' => '<p>Audit</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ], $overrides));
    }
}
