<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Services\Email\CampaignAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_service_records_campaign_activity(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Audit Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Audit',
            'content' => '<p>Audit</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $log = app(CampaignAuditService::class)->record(
            $campaign,
            'created',
            'Campaign created for testing.',
            ['source' => 'test']
        );

        $this->assertSame($campaign->id, $log->email_campaign_id);
        $this->assertSame('created', $log->action);
        $this->assertSame('Campaign created for testing.', $log->description);
        $this->assertSame(['source' => 'test'], $log->metadata);

        $this->assertDatabaseHas('email_campaign_activity_logs', [
            'email_campaign_id' => $campaign->id,
            'action' => 'created',
            'description' => 'Campaign created for testing.',
        ]);
    }
}
