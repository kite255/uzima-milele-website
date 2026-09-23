<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class EmailCampaignExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_contains_campaign_recipient_metrics(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Export Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Export Subject',
            'content' => '<p>Export</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 1,
            'sent_count' => 1,
            'failed_count' => 0,
            'sent_at' => now(),
        ]);

        EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Neema Test',
            'email' => 'neema@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now(),
            'first_opened_at' => now(),
            'first_clicked_at' => now(),
            'click_count' => 2,
        ]);

        $response = app(CampaignExportService::class)->csv($campaign);

        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $csv = (string) ob_get_clean();

        $this->assertStringContainsString('email,name,status,sent_at,failed_at,first_opened_at,last_opened_at,open_count,first_clicked_at,last_clicked_at,click_count,unsubscribed_at', $csv);
        $this->assertStringContainsString('neema@example.com', $csv);
        $this->assertStringContainsString('Neema Test', $csv);
        $this->assertStringContainsString(',sent,', $csv);
        $this->assertStringContainsString(',2,', $csv);
    }
}
