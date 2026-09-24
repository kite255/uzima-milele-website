<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailSubscriber;
use App\Models\EmailSuppression;
use App\Services\Email\CampaignSuppressionService;
use App\Services\EmailCampaignService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignAdvancedAudienceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_queue_campaign_uses_advanced_audience_and_excludes_suppressed_subscribers(): void
    {
        Carbon::setTestNow('2026-09-24 12:00:00');
        Queue::fake();

        $eligible = EmailSubscriber::query()->create([
            'name' => 'Eligible Subscriber',
            'email' => 'eligible@example.com',
            'status' => 'subscribed',
            'subscribed_at' => now()->subDays(2),
        ]);

        $old = EmailSubscriber::query()->create([
            'name' => 'Old Subscriber',
            'email' => 'old@example.com',
            'status' => 'subscribed',
            'subscribed_at' => now()->subDays(20),
        ]);

        $suppressed = EmailSubscriber::query()->create([
            'name' => 'Suppressed Subscriber',
            'email' => 'suppressed@example.com',
            'status' => 'subscribed',
            'subscribed_at' => now()->subDay(),
        ]);

        app(CampaignSuppressionService::class)->suppress(
            $suppressed->email,
            EmailSuppression::REASON_MANUAL,
            'admin'
        );

        $campaign = EmailCampaign::query()->create([
            'name' => 'Recent Subscribers',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Recent audience',
            'content' => '<p>Hello recent subscribers.</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'audience_filter_type' => 'new_7_days',
        ]);

        $campaign = app(EmailCampaignService::class)->queueCampaign($campaign);

        $this->assertSame(1, $campaign->total_recipients);

        $this->assertDatabaseHas('email_campaign_recipients', [
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $eligible->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('email_campaign_recipients', [
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $old->id,
        ]);

        $this->assertDatabaseMissing('email_campaign_recipients', [
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $suppressed->id,
        ]);

        Queue::assertPushed(SendEmailCampaign::class);
    }
}
