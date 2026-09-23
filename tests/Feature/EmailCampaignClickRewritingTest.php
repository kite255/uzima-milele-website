<?php

namespace Tests\Feature;

use App\Mail\CustomCampaignMail;
use App\Mail\DevotionCampaignMail;
use App\Models\Devotion;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignClickRewritingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_service_rewrites_only_safe_http_and_https_links(): void
    {
        $recipient = $this->recipient();
        $service = app(CampaignTrackingService::class);

        $html = <<<'HTML'
<p>
    <a href="https://uzimamilele.or.tz/tafakari/example">Safe HTTPS</a>
    <a href="http://uzimamilele.or.tz/example">Safe HTTP</a>
    <a href="mailto:info@uzimamilele.or.tz">Email</a>
    <a href="#section">Anchor</a>
</p>
HTML;

        $rewritten = $service->rewriteLinks($html, $recipient);

        $this->assertStringContainsString('/email/click/', $rewritten);
        $this->assertStringContainsString('mailto:info@uzimamilele.or.tz', $rewritten);
        $this->assertStringContainsString('href="#section"', $rewritten);
        $this->assertStringNotContainsString('href="https://uzimamilele.or.tz/tafakari/example"', $rewritten);
        $this->assertStringNotContainsString('href="http://uzimamilele.or.tz/example"', $rewritten);
    }

    public function test_custom_campaign_mail_rewrites_content_links_for_real_recipient(): void
    {
        $recipient = $this->recipient([
            'content' => '<p><a href="https://uzimamilele.or.tz/lessons">Soma somo</a></p>',
        ]);

        $html = (new CustomCampaignMail(
            $recipient->campaign,
            $recipient
        ))->render();

        $this->assertStringContainsString('/email/click/', $html);
        $this->assertStringNotContainsString(
            'href="https://uzimamilele.or.tz/lessons"',
            $html
        );
    }

    public function test_devotion_campaign_mail_uses_tracked_devotion_link(): void
    {
        $devotion = Devotion::query()->create([
            'title' => 'Mtema-miti — Bidii Inayoleta Matokeo',
            'slug' => 'mtema-miti-bidii-inayoleta-matokeo',
            'content' => '<p>Devotion content</p>',
            'published_at' => now()->toDateString(),
        ]);

        $recipient = $this->recipient([
            'type' => EmailCampaign::TYPE_DEVOTION,
            'devotion_id' => $devotion->id,
            'content' => null,
        ]);

        $campaign = $recipient->campaign->fresh('devotion');

        $html = (new DevotionCampaignMail(
            $campaign,
            $recipient
        ))->render();

        $this->assertStringContainsString('/email/click/', $html);
        $this->assertStringContainsString(
            urlencode(route('devotions.show', $devotion->slug)),
            $html
        );
    }

    private function recipient(array $campaignOverrides = []): EmailCampaignRecipient
    {
        $campaign = EmailCampaign::query()->create(array_merge([
            'name' => 'Tracked Links Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Tracked links',
            'content' => '<p>Hello</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENDING,
            'total_recipients' => 1,
            'sent_count' => 0,
            'failed_count' => 0,
        ], $campaignOverrides));

        return EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'name' => 'Tracked Recipient',
            'email' => 'tracked@example.com',
            'status' => EmailCampaignRecipient::STATUS_PENDING,
        ]);
    }
}
