<?php

namespace Tests\Feature;

use App\Models\Devotion;
use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailSubscriber;
use App\Models\EmailSubscriberGroup;
use App\Services\DevotionEmailAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DevotionEmailAutomationTest extends TestCase
{
    use RefreshDatabase;

 protected function createActiveSubscriber(
    string $email = 'subscriber@example.com'
): EmailSubscriber {
    return EmailSubscriber::query()->create([
        'name' => 'Test Subscriber',
        'email' => $email,
        'status' => 'subscribed',
        'unsubscribe_token' => hash(
            'sha256',
            $email . microtime(true) . random_int(1, PHP_INT_MAX)
        ),
        'subscribed_at' => now(),
    ]);
}

    protected function configureAutomation(): EmailSetting
    {
        $settings = EmailSetting::current();

        $settings->update([
            'auto_schedule_devotions' => true,
            'default_devotion_send_time' => '06:00',
            'default_recipient_scope' => 'subscribed',
            'email_subscriber_group_id' => null,
        ]);

        return $settings;
    }

    public function test_future_devotion_creates_one_scheduled_email_campaign(): void
    {
        Queue::fake();

        $this->createActiveSubscriber();
        $this->configureAutomation();

        $devotion = Devotion::query()->create([
            'title' => 'Tumaini Katika Mungu',
            'slug' => 'tumaini-katika-mungu',
            'content' => '<p>Tumaini katika Mungu kila siku.</p>',
            'published_at' => now()
                ->addDays(2)
                ->toDateString(),
            'email_send_time' => '06:00',
        ]);

        $campaign = app(
            DevotionEmailAutomationService::class
        )->sync($devotion);

        $this->assertNotNull($campaign);

        $this->assertSame(
            EmailCampaign::TYPE_DEVOTION,
            $campaign->type
        );

        $this->assertSame(
            $devotion->id,
            $campaign->devotion_id
        );

        $this->assertSame(
            EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            $campaign->recipient_scope
        );

        $this->assertSame(
            EmailCampaign::STATUS_SCHEDULED,
            $campaign->status
        );

        $this->assertSame(
            1,
            $devotion
                ->emailCampaigns()
                ->count()
        );

        $this->assertSame(
            $devotion->published_at
                ->format('Y-m-d')
            . ' 06:00',
            $campaign->scheduled_at
                ->format('Y-m-d H:i')
        );

        $this->assertSame(
            1,
            $campaign->total_recipients
        );
    }

    public function test_syncing_same_devotion_twice_does_not_create_duplicate_campaign(): void
    {
        Queue::fake();

        $this->createActiveSubscriber();
        $this->configureAutomation();

        $devotion = Devotion::query()->create([
            'title' => 'Nguvu ya Maombi',
            'slug' => 'nguvu-ya-maombi',
            'published_at' => now()
                ->addDays(3)
                ->toDateString(),
            'email_send_time' => '06:00',
        ]);

        $service = app(
            DevotionEmailAutomationService::class
        );

        $first = $service->sync(
            $devotion
        );

        $second = $service->sync(
            $devotion->fresh()
        );

        $this->assertSame(
            $first->id,
            $second->id
        );

        $this->assertSame(
            1,
            EmailCampaign::query()
                ->where(
                    'devotion_id',
                    $devotion->id
                )
                ->count()
        );
    }

    public function test_sent_devotion_campaign_is_not_rescheduled_or_resent(): void
    {
        Queue::fake();

        $this->createActiveSubscriber();
        $this->configureAutomation();

        $devotion = Devotion::query()->create([
            'title' => 'Amani ya Kristo',
            'slug' => 'amani-ya-kristo',
            'published_at' => now()
                ->addDays(4)
                ->toDateString(),
            'email_send_time' => '06:00',
        ]);

        $service = app(
            DevotionEmailAutomationService::class
        );

        $campaign = $service->sync(
            $devotion
        );

        $campaign->update([
            'status' =>
                EmailCampaign::STATUS_SENT,

            'sent_at' =>
                now(),
        ]);

        $originalScheduledAt =
            $campaign->scheduled_at?->copy();

        $devotion->update([
            'title' =>
                'Amani ya Kristo Iliyohariri',

            'email_send_time' =>
                '08:30',
        ]);

        $result = $service->sync(
            $devotion->fresh()
        );

        $campaign->refresh();

        $this->assertSame(
            $campaign->id,
            $result->id
        );

        $this->assertSame(
            EmailCampaign::STATUS_SENT,
            $campaign->status
        );

        $this->assertNotNull(
            $campaign->sent_at
        );

        $this->assertSame(
            $originalScheduledAt?->format(
                'Y-m-d H:i'
            ),
            $campaign->scheduled_at?->format(
                'Y-m-d H:i'
            )
        );

        $this->assertSame(
            1,
            EmailCampaign::query()
                ->where(
                    'devotion_id',
                    $devotion->id
                )
                ->count()
        );
    }

    public function test_disabled_automation_does_not_create_campaign(): void
    {
        Queue::fake();

        $this->createActiveSubscriber();

        $settings = EmailSetting::current();

        $settings->update([
            'auto_schedule_devotions' => false,
            'default_devotion_send_time' => '06:00',
            'default_recipient_scope' => 'subscribed',
            'email_subscriber_group_id' => null,
        ]);

        $devotion = Devotion::query()->create([
            'title' => 'Neema ya Mungu',
            'slug' => 'neema-ya-mungu',
            'published_at' => now()
                ->addDays(2)
                ->toDateString(),
            'email_send_time' => '06:00',
        ]);

        $result = app(
            DevotionEmailAutomationService::class
        )->sync($devotion);

        $this->assertNull($result);

        $this->assertSame(
            0,
            $devotion
                ->emailCampaigns()
                ->count()
        );
    }

    public function test_group_default_audience_is_used_for_devotion_campaign(): void
    {
        Queue::fake();

        $member = $this->createActiveSubscriber(
            'member@example.com'
        );

        $this->createActiveSubscriber(
            'outside@example.com'
        );

        $group = EmailSubscriberGroup::query()->create([
            'name' => 'Morning Devotion',
        ]);

        $group
            ->subscribers()
            ->attach($member->id);

        $settings = EmailSetting::current();

        $settings->update([
            'auto_schedule_devotions' => true,
            'default_devotion_send_time' => '06:00',
            'default_recipient_scope' =>
                EmailCampaign::RECIPIENT_SCOPE_GROUP,
            'email_subscriber_group_id' =>
                $group->id,
        ]);

        $devotion = Devotion::query()->create([
            'title' => 'Mungu Ni Mwaminifu',
            'slug' => 'mungu-ni-mwaminifu',
            'published_at' => now()
                ->addDays(2)
                ->toDateString(),
            'email_send_time' => '06:00',
        ]);

        $campaign = app(
            DevotionEmailAutomationService::class
        )->sync($devotion);

        $this->assertNotNull($campaign);

        $this->assertSame(
            EmailCampaign::RECIPIENT_SCOPE_GROUP,
            $campaign->recipient_scope
        );

        $this->assertSame(
            $group->id,
            $campaign->email_subscriber_group_id
        );

        $this->assertSame(
            1,
            $campaign->total_recipients
        );

        $this->assertDatabaseHas(
            'email_campaign_recipients',
            [
                'email_campaign_id' =>
                    $campaign->id,

                'email_subscriber_id' =>
                    $member->id,
            ]
        );
    }
}