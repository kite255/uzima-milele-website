<?php

namespace Tests\Feature;

use App\Models\Devotion;
use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailSubscriber;
use App\Services\DevotionEmailAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DevotionEmailAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_devotion_creates_one_scheduled_email_campaign(): void
    {
        Queue::fake();

        EmailSubscriber::query()->create([
            'name' => 'Test Subscriber',
            'email' => 'subscriber@example.com',
            'status' => 'subscribed',
            'unsubscribe_token' => str_repeat('a', 64),
            'subscribed_at' => now(),
        ]);

        $settings = EmailSetting::current();

        $settings->update([
            'auto_schedule_devotions' => true,
            'default_devotion_send_time' => '06:00',
            'default_recipient_scope' => 'subscribed',
            'email_subscriber_group_id' => null,
        ]);

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
}