<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Services\Email\CampaignPreviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailCampaignTestSendTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_renders_campaign_without_creating_recipients_or_changing_counters(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Preview Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Preview Subject',
            'content' => '<p>Habari {{first_name}} - {{email}}</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        $html = app(CampaignPreviewService::class)->render($campaign);

        $this->assertStringContainsString('Preview Subject', $html);
        $this->assertStringNotContainsString('{{first_name}}', $html);
        $this->assertStringNotContainsString('{{email}}', $html);
        $this->assertSame(0, $campaign->recipients()->count());
        $this->assertSame(0, $campaign->fresh()->sent_count);
        $this->assertSame(0, $campaign->fresh()->failed_count);
    }

    public function test_test_send_sends_actual_campaign_design_without_creating_recipients_or_analytics(): void
    {
        Mail::fake();

        $campaign = EmailCampaign::query()->create([
            'name' => 'Test Send Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Test Send Subject',
            'content' => '<p>Hello {{name}}</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        app(CampaignPreviewService::class)->sendTest(
            $campaign,
            ['admin1@example.com', 'admin2@example.com']
        );

        Mail::assertSentCount(2);

        $this->assertSame(0, $campaign->recipients()->count());
        $this->assertSame(0, $campaign->fresh()->total_recipients);
        $this->assertSame(0, $campaign->fresh()->sent_count);
        $this->assertSame(0, $campaign->fresh()->failed_count);
    }
}
