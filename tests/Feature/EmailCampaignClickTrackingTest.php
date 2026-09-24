<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class EmailCampaignClickTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_click_is_recorded_and_redirects(): void
    {
        $recipient = $this->recipient();
        $destination = 'https://uzimamilele.or.tz/tafakari/example';

        $url = app(CampaignTrackingService::class)->trackableUrl(
            $recipient,
            $destination
        );

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.10',
                'HTTP_USER_AGENT' => 'CampaignClickTest/1.0',
            ])
            ->get($url);

        $response->assertRedirect($destination);

        $this->assertDatabaseHas('email_campaign_clicks', [
            'email_campaign_id' => $recipient->email_campaign_id,
            'email_campaign_recipient_id' => $recipient->id,
            'url' => $destination,
            'user_agent' => 'CampaignClickTest/1.0',
            'ip_hash' => hash('sha256', '203.0.113.10'),
        ]);

        $recipient->refresh();

        $this->assertSame(1, $recipient->click_count);
        $this->assertNotNull($recipient->first_clicked_at);
        $this->assertNotNull($recipient->last_clicked_at);
    }

    public function test_repeated_click_keeps_first_clicked_at_and_updates_last_clicked_at(): void
    {
        Carbon::setTestNow('2026-09-24 09:00:00');

        $recipient = $this->recipient();
        $destination = 'https://uzimamilele.or.tz/lessons/example';
        $service = app(CampaignTrackingService::class);

        $service->recordClick(
            $recipient,
            $destination,
            'First Agent',
            '203.0.113.11'
        );

        $recipient->refresh();
        $firstClickedAt = $recipient->first_clicked_at?->copy();

        Carbon::setTestNow('2026-09-24 09:05:00');

        $service->recordClick(
            $recipient,
            $destination,
            'Second Agent',
            '203.0.113.11'
        );

        $recipient->refresh();

        $this->assertSame(2, $recipient->click_count);
        $this->assertTrue($recipient->first_clicked_at?->equalTo($firstClickedAt));
        $this->assertTrue(
            $recipient->last_clicked_at?->equalTo(Carbon::parse('2026-09-24 09:05:00'))
        );
        $this->assertSame(2, $recipient->clicks()->count());

        Carbon::setTestNow();
    }

    public function test_tampered_click_url_fails_without_recording_click(): void
    {
        $recipient = $this->recipient();

        $url = app(CampaignTrackingService::class)->trackableUrl(
            $recipient,
            'https://uzimamilele.or.tz/tafakari/example'
        );

        $separator = str_contains($url, '?') ? '&' : '?';
        $tampered = $url.$separator.'tampered=1';

        $this->get($tampered)->assertNotFound();

        $this->assertDatabaseCount('email_campaign_clicks', 0);
        $this->assertSame(0, $recipient->fresh()->click_count);
    }

    public function test_invalid_tracking_token_returns_not_found(): void
    {
        $this->get('/email/click/invalid-token')->assertNotFound();
        $this->assertDatabaseCount('email_campaign_clicks', 0);
    }

    public function test_unsafe_or_malformed_destinations_are_rejected(): void
    {
        $recipient = $this->recipient();
        $service = app(CampaignTrackingService::class);

        foreach ([
            'javascript:alert(1)',
            'data:text/html;base64,SGVsbG8=',
            'mailto:test@example.com',
            '/relative/path',
            'not-a-url',
        ] as $destination) {
            try {
                $service->trackableUrl($recipient, $destination);
                $this->fail("Unsafe destination was accepted: {$destination}");
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_raw_ip_address_is_never_stored(): void
    {
        $recipient = $this->recipient();
        $ip = '203.0.113.99';

        app(CampaignTrackingService::class)->recordClick(
            $recipient,
            'https://uzimamilele.or.tz/example',
            'Privacy Test',
            $ip
        );

        $click = $recipient->clicks()->firstOrFail();

        $this->assertSame(hash('sha256', $ip), $click->ip_hash);
        $this->assertNotSame($ip, $click->ip_hash);
        $this->assertStringNotContainsString($ip, (string) $click->ip_hash);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function recipient(): EmailCampaignRecipient
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Click Tracking Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Click tracking',
            'content' => '<p><a href="https://uzimamilele.or.tz/example">Read</a></p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'total_recipients' => 1,
            'sent_count' => 1,
            'failed_count' => 0,
            'sent_at' => now(),
        ]);

        return EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Click Recipient',
            'email' => 'click-recipient@example.com',
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }
}
