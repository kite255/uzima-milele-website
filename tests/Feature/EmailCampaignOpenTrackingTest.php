<?php

namespace Tests\Feature;

use App\Mail\CustomCampaignMail;
use App\Mail\DevotionCampaignMail;
use App\Models\Devotion;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Services\EmailCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignOpenTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function createCampaign(): EmailCampaign
    {
        return EmailCampaign::query()->create([
            'name' => 'Open Tracking Test',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Test Subject',
            'content' => '<p>Test content</p>',
            'recipient_scope' =>
                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);
    }

    public function test_recipient_has_unique_tracking_token(): void
    {
        $campaign = $this->createCampaign();

        $recipient = EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Test Subscriber',
            'email' => 'test@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
        ]);

        $this->assertNotEmpty(
            $recipient->fresh()->tracking_token
        );
    }

    public function test_first_open_is_recorded(): void
    {
        $campaign = $this->createCampaign();

        $recipient = EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Test Subscriber',
            'email' => 'test@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
        ]);

        $recipient->markAsOpened();

        $recipient->refresh();

        $this->assertNotNull(
            $recipient->first_opened_at
        );

        $this->assertNotNull(
            $recipient->last_opened_at
        );

        $this->assertSame(
            1,
            $recipient->open_count
        );
    }

    public function test_repeated_open_does_not_replace_first_open_time(): void
    {
        $campaign = $this->createCampaign();

        $recipient = EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Test Subscriber',
            'email' => 'test@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
        ]);

        $recipient->markAsOpened();

        $firstOpenedAt = $recipient
            ->fresh()
            ->first_opened_at;

        $this->travel(5)->minutes();

        $recipient->markAsOpened();

        $recipient->refresh();

        $this->assertTrue(
            $recipient
                ->first_opened_at
                ->equalTo($firstOpenedAt)
        );

        $this->assertTrue(
            $recipient
                ->last_opened_at
                ->greaterThan($firstOpenedAt)
        );

        $this->assertSame(
            2,
            $recipient->open_count
        );
    }

    public function test_scheduled_campaign_recipient_snapshot_has_tracking_token(): void
    {
        EmailSubscriber::query()->create([
            'name' => 'Snapshot Subscriber',
            'email' => 'snapshot@example.com',
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);

        $campaign = EmailCampaign::query()->create([
            'name' => 'Snapshot Tracking Test',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Snapshot Test',
            'content' => '<p>Tracking test</p>',
            'recipient_scope' =>
                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        app(EmailCampaignService::class)
            ->scheduleCampaign(
                $campaign,
                now()->addHour()
            );

        $recipient = EmailCampaignRecipient::query()
            ->where(
                'email_campaign_id',
                $campaign->id
            )
            ->firstOrFail();

        $this->assertNotEmpty(
            $recipient->tracking_token
        );
    }


    public function test_tracking_endpoint_records_open_and_returns_gif(): void
{
    $campaign = $this->createCampaign();

    $recipient = EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Tracked Subscriber',
        'email' => 'tracked@example.com',
        'status' => EmailCampaignRecipient::STATUS_SENT,
    ]);

    $response = $this->get(
        route(
            'email-campaigns.open',
            $recipient->tracking_token
        )
    );

    $response
        ->assertOk()
        ->assertHeader(
            'Content-Type',
            'image/gif'
        );

    $recipient->refresh();

    $this->assertSame(
        1,
        $recipient->open_count
    );

    $this->assertNotNull(
        $recipient->first_opened_at
    );

    $this->assertNotNull(
        $recipient->last_opened_at
    );
}

public function test_invalid_tracking_token_still_returns_gif_without_error(): void
{
    $response = $this->get(
        route(
            'email-campaigns.open',
            'invalid-tracking-token'
        )
    );

    $response
        ->assertOk()
        ->assertHeader(
            'Content-Type',
            'image/gif'
        );
}

public function test_custom_campaign_email_contains_tracking_pixel(): void
{
    $campaign = $this->createCampaign();

    $recipient = EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Tracked Subscriber',
        'email' => 'tracked@example.com',
        'status' => EmailCampaignRecipient::STATUS_SENT,
    ]);

    $html = (new CustomCampaignMail(
        campaign: $campaign,
        recipient: $recipient,
    ))->render();

    $trackingUrl = route(
        'email-campaigns.open',
        $recipient->tracking_token
    );

    $this->assertStringContainsString(
        $trackingUrl,
        $html
    );
}

public function test_devotion_campaign_email_contains_tracking_pixel(): void
{
    $devotion = Devotion::query()->create([
        'title' => 'Tracking Devotion',
        'slug' => 'tracking-devotion',
        'content' => '<p>Devotion content</p>',
        'published_at' => now(),
    ]);

    $campaign = EmailCampaign::query()->create([
        'name' => 'Devotion Tracking Test',
        'type' => EmailCampaign::TYPE_DEVOTION,
        'devotion_id' => $devotion->id,
        'subject' => 'Devotion Tracking',
        'recipient_scope' =>
            EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
        'status' => EmailCampaign::STATUS_DRAFT,
    ]);

    $recipient = EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Tracked Subscriber',
        'email' => 'tracked@example.com',
        'status' => EmailCampaignRecipient::STATUS_SENT,
    ]);

    $html = (new DevotionCampaignMail(
        campaign: $campaign,
        recipient: $recipient,
    ))->render();

    $trackingUrl = route(
        'email-campaigns.open',
        $recipient->tracking_token
    );

    $this->assertStringContainsString(
        $trackingUrl,
        $html
    );
}

public function test_campaign_counts_unique_opened_recipients(): void
{
    $campaign = $this->createCampaign();

    $opened = EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Opened Subscriber',
        'email' => 'opened@example.com',
        'status' => EmailCampaignRecipient::STATUS_SENT,
    ]);

    EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Not Opened Subscriber',
        'email' => 'not-opened@example.com',
        'status' => EmailCampaignRecipient::STATUS_SENT,
    ]);

    $opened->markAsOpened();
    $opened->markAsOpened();

    $this->assertSame(
        1,
        $campaign->openedRecipientsCount()
    );
}

public function test_campaign_counts_unopened_sent_recipients(): void
{
    $campaign = $this->createCampaign();

    $opened = EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Opened Subscriber',
        'email' => 'opened@example.com',
        'status' => EmailCampaignRecipient::STATUS_SENT,
    ]);

    EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Not Opened Subscriber',
        'email' => 'not-opened@example.com',
        'status' => EmailCampaignRecipient::STATUS_SENT,
    ]);

    EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Failed Subscriber',
        'email' => 'failed@example.com',
        'status' => EmailCampaignRecipient::STATUS_FAILED,
    ]);

    $opened->markAsOpened();

    $this->assertSame(
        1,
        $campaign->unopenedRecipientsCount()
    );
}

public function test_campaign_open_rate_uses_sent_recipients_only(): void
{
    $campaign = $this->createCampaign();

    for ($i = 1; $i <= 4; $i++) {
        $recipient = EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Subscriber ' . $i,
            'email' => 'subscriber' . $i . '@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
        ]);

        if ($i <= 3) {
            $recipient->markAsOpened();
        }
    }

    EmailCampaignRecipient::query()->create([
        'email_campaign_id' => $campaign->id,
        'name' => 'Failed Subscriber',
        'email' => 'failed@example.com',
        'status' => EmailCampaignRecipient::STATUS_FAILED,
    ]);

    $this->assertSame(
        75.0,
        $campaign->openRate()
    );
}

}