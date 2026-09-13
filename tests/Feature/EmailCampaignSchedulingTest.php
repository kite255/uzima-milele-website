<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_can_be_created_as_scheduled(): void
    {
        $user = User::factory()->create();

        $scheduledAt = now()->addHour();

        $campaign = EmailCampaign::create([
            'name' => 'Scheduled Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Scheduled Email',
            'content' => '<p>Hello</p>',
            'recipient_scope' => 'subscribed',
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'created_by' => $user->id,
        ]);

        $campaign->refresh();

        $this->assertSame(
            'scheduled',
            $campaign->status
        );

        $this->assertNotNull(
            $campaign->scheduled_at
        );
    }

    public function test_scheduled_campaign_is_not_ready_before_scheduled_time(): void
    {
        $campaign = new EmailCampaign([
            'name' => 'Future Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Future Email',
            'content' => '<p>Hello</p>',
            'recipient_scope' => 'subscribed',
            'status' => 'scheduled',
            'scheduled_at' => now()->addHour(),
        ]);

        $this->assertFalse(
            $campaign->isDueForSending()
        );
    }

    public function test_scheduled_campaign_is_ready_after_scheduled_time(): void
    {
        $campaign = new EmailCampaign([
            'name' => 'Due Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Due Email',
            'content' => '<p>Hello</p>',
            'recipient_scope' => 'subscribed',
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->assertTrue(
            $campaign->isDueForSending()
        );
    }
}