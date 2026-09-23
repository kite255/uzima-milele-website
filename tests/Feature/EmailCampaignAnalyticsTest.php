<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_exact_expected_metrics(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Analytics Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Analytics',
            'content' => '<p>Analytics</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 5,
            'sent_count' => 3,
            'failed_count' => 1,
            'sent_at' => now(),
        ]);

        $this->recipient($campaign, EmailCampaignRecipient::STATUS_SENT, [
            'first_opened_at' => now(),
            'first_clicked_at' => now(),
            'click_count' => 1,
        ]);
        $this->recipient($campaign, EmailCampaignRecipient::STATUS_SENT, [
            'first_opened_at' => now(),
        ]);
        $this->recipient($campaign, EmailCampaignRecipient::STATUS_SENT);
        $this->recipient($campaign, EmailCampaignRecipient::STATUS_FAILED);
        $this->recipient($campaign, EmailCampaignRecipient::STATUS_SUPPRESSED, [
            'unsubscribed_at' => now(),
        ]);

        $summary = app(CampaignAnalyticsService::class)->summary($campaign);

        $this->assertSame([
            'total_recipients', 'sent', 'pending', 'failed', 'suppressed',
            'opened', 'no_tracked_open', 'clicked', 'unsubscribed',
            'send_success_rate', 'open_rate', 'click_rate',
            'click_to_open_rate', 'unsubscribe_rate',
        ], array_keys($summary));

        $this->assertSame(5, $summary['total_recipients']);
        $this->assertSame(3, $summary['sent']);
        $this->assertSame(0, $summary['pending']);
        $this->assertSame(1, $summary['failed']);
        $this->assertSame(1, $summary['suppressed']);
        $this->assertSame(2, $summary['opened']);
        $this->assertSame(1, $summary['no_tracked_open']);
        $this->assertSame(1, $summary['clicked']);
        $this->assertSame(1, $summary['unsubscribed']);
        $this->assertSame(60.0, $summary['send_success_rate']);
        $this->assertSame(66.7, $summary['open_rate']);
        $this->assertSame(33.3, $summary['click_rate']);
        $this->assertSame(50.0, $summary['click_to_open_rate']);
        $this->assertSame(20.0, $summary['unsubscribe_rate']);
    }

    private function recipient(
        EmailCampaign $campaign,
        string $status,
        array $overrides = []
    ): EmailCampaignRecipient {
        return EmailCampaignRecipient::query()->create(array_merge([
            'email_campaign_id' => $campaign->id,
            'name' => 'Recipient',
            'email' => fake()->unique()->safeEmail(),
            'status' => $status,
            'sent_at' => $status === EmailCampaignRecipient::STATUS_SENT ? now() : null,
        ], $overrides));
    }
}
