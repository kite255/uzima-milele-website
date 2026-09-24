<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedEmailCampaignBackwardCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_sent_campaign_is_treated_as_completed_history(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Historical Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Historical Subject',
            'content' => '<p>Historical content</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 1,
            'sent_count' => 1,
            'failed_count' => 0,
            'sent_at' => now()->subDay(),
        ]);

        $this->assertTrue($campaign->isCompletedHistory());
    }

    public function test_existing_subscriber_unsubscribe_token_remains_usable(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Legacy Subscriber',
            'email' => 'legacy@example.com',
            'status' => 'subscribed',
            'unsubscribe_token' => 'legacy-token-123',
        ]);

        $response = $this->get(route('email-subscribers.unsubscribe', [
            'token' => $subscriber->unsubscribe_token,
        ]));

        $response->assertSuccessful();
        $this->assertSame('unsubscribed', $subscriber->fresh()->status);
    }

    public function test_existing_open_tracking_token_remains_usable(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Legacy Open Tracking',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Open Tracking',
            'content' => '<p>Content</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 1,
            'sent_count' => 1,
            'failed_count' => 0,
        ]);

        $recipient = EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Legacy Recipient',
            'email' => 'legacy-recipient@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'tracking_token' => 'legacy-open-token-123',
            'sent_at' => now(),
        ]);

        $response = $this->get(route('email-campaigns.open', [
            'token' => $recipient->tracking_token,
        ]));

        $response->assertSuccessful();
        $this->assertNotNull($recipient->fresh()->first_opened_at);
    }
}
