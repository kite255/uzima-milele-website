<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Models\EmailSuppression;
use App\Services\Email\CampaignAudienceService;
use App\Services\Email\CampaignSuppressionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignAdvancedAudienceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_recent_subscriber_filters_cover_7_days_30_days_and_subscribed_after(): void
    {
        Carbon::setTestNow('2026-09-24 12:00:00');

        $recent = $this->subscriber('recent@example.com', now()->subDays(3));
        $withinThirty = $this->subscriber('within30@example.com', now()->subDays(20));
        $old = $this->subscriber('old@example.com', now()->subDays(60));

        $service = app(CampaignAudienceService::class);

        $sevenDayCampaign = $this->campaign('new_7_days');
        $this->assertSame(
            [$recent->id],
            $service->subscriberQuery($sevenDayCampaign)->orderBy('id')->pluck('id')->all()
        );

        $thirtyDayCampaign = $this->campaign('new_30_days');
        $this->assertSame(
            [$recent->id, $withinThirty->id],
            $service->subscriberQuery($thirtyDayCampaign)->orderBy('id')->pluck('id')->all()
        );

        $afterCampaign = $this->campaign('subscribed_after', '2026-09-10');
        $this->assertSame(
            [$recent->id],
            $service->subscriberQuery($afterCampaign)->orderBy('id')->pluck('id')->all()
        );

        $this->assertNotContains($old->id, $service->subscriberQuery($thirtyDayCampaign)->pluck('id')->all());
    }

    public function test_never_received_and_never_opened_filters_use_campaign_history(): void
    {
        $neverReceived = $this->subscriber('never-received@example.com', now()->subDays(100));
        $receivedButNeverOpened = $this->subscriber('never-opened@example.com', now()->subDays(100));
        $opened = $this->subscriber('opened@example.com', now()->subDays(100));

        $history = $this->sourceCampaign();

        $this->recipient($history, $receivedButNeverOpened, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDays(20),
        ]);

        $this->recipient($history, $opened, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDays(20),
            'first_opened_at' => now()->subDays(19),
            'last_opened_at' => now()->subDays(19),
            'open_count' => 1,
        ]);

        $service = app(CampaignAudienceService::class);

        $neverReceivedCampaign = $this->campaign('never_received');
        $this->assertSame(
            [$neverReceived->id],
            $service->subscriberQuery($neverReceivedCampaign)->orderBy('id')->pluck('id')->all()
        );

        $neverOpenedCampaign = $this->campaign('never_opened');
        $neverOpenedIds = $service->subscriberQuery($neverOpenedCampaign)->orderBy('id')->pluck('id')->all();

        $this->assertContains($neverReceived->id, $neverOpenedIds);
        $this->assertContains($receivedButNeverOpened->id, $neverOpenedIds);
        $this->assertNotContains($opened->id, $neverOpenedIds);
    }

    public function test_opened_and_not_opened_selected_campaign_filters_only_original_recipients(): void
    {
        $opened = $this->subscriber('opened-selected@example.com');
        $notOpened = $this->subscriber('not-opened-selected@example.com');
        $notInCampaign = $this->subscriber('not-in-campaign@example.com');

        $source = $this->sourceCampaign();

        $this->recipient($source, $opened, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDay(),
            'first_opened_at' => now()->subHours(20),
            'last_opened_at' => now()->subHours(20),
            'open_count' => 1,
        ]);

        $this->recipient($source, $notOpened, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDay(),
        ]);

        $service = app(CampaignAudienceService::class);

        $openedCampaign = $this->campaign('opened_campaign', (string) $source->id);
        $this->assertSame(
            [$opened->id],
            $service->subscriberQuery($openedCampaign)->pluck('id')->all()
        );

        $notOpenedCampaign = $this->campaign('not_opened_campaign', (string) $source->id);
        $this->assertSame(
            [$notOpened->id],
            $service->subscriberQuery($notOpenedCampaign)->pluck('id')->all()
        );

        $this->assertNotContains($notInCampaign->id, $service->subscriberQuery($notOpenedCampaign)->pluck('id')->all());
    }

    public function test_not_opened_selected_campaign_excludes_suppressed_subscribers(): void
    {
        $subscriber = $this->subscriber('suppressed-non-opener@example.com');
        $source = $this->sourceCampaign();

        $this->recipient($source, $subscriber, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDay(),
        ]);

        app(CampaignSuppressionService::class)->suppress(
            $subscriber->email,
            EmailSuppression::REASON_MANUAL,
            'admin'
        );

        $campaign = $this->campaign('not_opened_campaign', (string) $source->id);

        $this->assertSame(
            [],
            app(CampaignAudienceService::class)
                ->subscriberQuery($campaign)
                ->pluck('id')
                ->all()
        );
    }

    public function test_clicked_and_not_clicked_selected_campaign_filters_use_recipient_engagement(): void
    {
        $clicked = $this->subscriber('clicked@example.com');
        $notClicked = $this->subscriber('not-clicked@example.com');
        $notInCampaign = $this->subscriber('click-not-in-campaign@example.com');

        $source = $this->sourceCampaign();

        $this->recipient($source, $clicked, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDay(),
            'first_clicked_at' => now()->subHours(10),
            'last_clicked_at' => now()->subHours(10),
            'click_count' => 1,
        ]);

        $this->recipient($source, $notClicked, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDay(),
        ]);

        $service = app(CampaignAudienceService::class);

        $clickedCampaign = $this->campaign('clicked_campaign', (string) $source->id);
        $this->assertSame(
            [$clicked->id],
            $service->subscriberQuery($clickedCampaign)->pluck('id')->all()
        );

        $notClickedCampaign = $this->campaign('not_clicked_campaign', (string) $source->id);
        $this->assertSame(
            [$notClicked->id],
            $service->subscriberQuery($notClickedCampaign)->pluck('id')->all()
        );

        $this->assertNotContains($notInCampaign->id, $service->subscriberQuery($notClickedCampaign)->pluck('id')->all());
    }

    public function test_inactive_30_60_and_90_day_filters_require_no_recent_tracked_engagement(): void
    {
        Carbon::setTestNow('2026-09-24 12:00:00');

        $inactive100 = $this->subscriber('inactive100@example.com', now()->subDays(120));
        $inactive70 = $this->subscriber('inactive70@example.com', now()->subDays(100));
        $inactive40 = $this->subscriber('inactive40@example.com', now()->subDays(80));
        $recentlyActive = $this->subscriber('recently-active@example.com', now()->subDays(120));
        $tooNew = $this->subscriber('too-new@example.com', now()->subDays(10));

        $history = $this->sourceCampaign();

        $this->recipient($history, $inactive100, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDays(110),
            'last_opened_at' => now()->subDays(100),
            'first_opened_at' => now()->subDays(100),
            'open_count' => 1,
        ]);

        $this->recipient($history, $inactive70, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDays(80),
            'last_clicked_at' => now()->subDays(70),
            'first_clicked_at' => now()->subDays(70),
            'click_count' => 1,
        ]);

        $this->recipient($history, $inactive40, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDays(50),
            'last_opened_at' => now()->subDays(40),
            'first_opened_at' => now()->subDays(40),
            'open_count' => 1,
        ]);

        $this->recipient($history, $recentlyActive, [
            'status' => EmailCampaignRecipient::STATUS_SENT,
            'sent_at' => now()->subDays(20),
            'last_opened_at' => now()->subDays(5),
            'first_opened_at' => now()->subDays(5),
            'open_count' => 1,
        ]);

        $service = app(CampaignAudienceService::class);

        $inactive30Ids = $service->subscriberQuery($this->campaign('inactive_30_days'))->orderBy('id')->pluck('id')->all();
        $inactive60Ids = $service->subscriberQuery($this->campaign('inactive_60_days'))->orderBy('id')->pluck('id')->all();
        $inactive90Ids = $service->subscriberQuery($this->campaign('inactive_90_days'))->orderBy('id')->pluck('id')->all();

        $this->assertSame([$inactive100->id, $inactive70->id, $inactive40->id], $inactive30Ids);
        $this->assertSame([$inactive100->id, $inactive70->id], $inactive60Ids);
        $this->assertSame([$inactive100->id], $inactive90Ids);
        $this->assertNotContains($recentlyActive->id, $inactive30Ids);
        $this->assertNotContains($tooNew->id, $inactive30Ids);
    }

    public function test_snapshot_creates_only_pending_currently_eligible_recipients(): void
    {
        $eligible = $this->subscriber('eligible@example.com', now()->subDays(2));
        $suppressed = $this->subscriber('suppressed@example.com', now()->subDays(2));
        $unsubscribed = $this->subscriber('unsubscribed@example.com', now()->subDays(2));
        $unsubscribed->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        app(CampaignSuppressionService::class)->suppress(
            $suppressed->email,
            EmailSuppression::REASON_MANUAL,
            'admin'
        );

        $campaign = $this->campaign('new_7_days');

        $created = app(CampaignAudienceService::class)->snapshot($campaign);

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('email_campaign_recipients', [
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $eligible->id,
            'email' => 'eligible@example.com',
            'status' => EmailCampaignRecipient::STATUS_PENDING,
        ]);
        $this->assertDatabaseMissing('email_campaign_recipients', [
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $suppressed->id,
        ]);
        $this->assertDatabaseMissing('email_campaign_recipients', [
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $unsubscribed->id,
        ]);
    }

    private function subscriber(string $email, ?Carbon $subscribedAt = null): EmailSubscriber
    {
        return EmailSubscriber::query()->create([
            'name' => ucfirst(strtok($email, '@')),
            'email' => $email,
            'status' => 'subscribed',
            'subscribed_at' => $subscribedAt ?? now()->subDays(100),
        ]);
    }

    private function campaign(string $filterType, ?string $filterValue = null): EmailCampaign
    {
        return EmailCampaign::query()->create([
            'name' => 'Segment '.$filterType,
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Audience test',
            'content' => 'Test content',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
            'audience_filter_type' => $filterType,
            'audience_filter_value' => $filterValue,
        ]);
    }

    private function sourceCampaign(): EmailCampaign
    {
        return EmailCampaign::query()->create([
            'name' => 'Source Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Source',
            'content' => 'Source content',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENT,
            'sent_at' => now()->subDay(),
        ]);
    }

    private function recipient(
        EmailCampaign $campaign,
        EmailSubscriber $subscriber,
        array $attributes = []
    ): EmailCampaignRecipient {
        return EmailCampaignRecipient::query()->create(array_merge([
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $subscriber->id,
            'name' => $subscriber->name,
            'email' => $subscriber->email,
            'status' => EmailCampaignRecipient::STATUS_SENT,
        ], $attributes));
    }
}
