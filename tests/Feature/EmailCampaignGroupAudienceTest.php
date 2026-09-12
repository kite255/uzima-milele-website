<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailSubscriber;
use App\Models\EmailSubscriberGroup;
use App\Services\EmailCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignGroupAudienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_scope_snapshots_only_active_members_of_selected_group(): void
    {
        Queue::fake();

        $activeOne = EmailSubscriber::create([
            'name' => 'Active One',
            'email' => 'active-one@example.test',
            'status' => 'subscribed',
        ]);

        $activeTwo = EmailSubscriber::create([
            'name' => 'Active Two',
            'email' => 'active-two@example.test',
            'status' => 'subscribed',
        ]);

        $unsubscribed = EmailSubscriber::create([
            'name' => 'Unsubscribed Member',
            'email' => 'unsubscribed@example.test',
            'status' => 'unsubscribed',
        ]);

        $outsideGroup = EmailSubscriber::create([
            'name' => 'Outside Group',
            'email' => 'outside@example.test',
            'status' => 'subscribed',
        ]);

        $group = EmailSubscriberGroup::create([
            'name' => 'Morning Devotion',
        ]);

        $group->subscribers()->sync([
            $activeOne->id,
            $activeTwo->id,
            $unsubscribed->id,
        ]);

        $campaign = EmailCampaign::create([
            'name' => 'Morning Devotion Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Morning Devotion',
            'content' => '<p>Hello group.</p>',
            'recipient_scope' => 'group',
            'email_subscriber_group_id' => $group->id,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $campaign = app(
            EmailCampaignService::class
        )->queueCampaign($campaign);

        $this->assertSame(
            2,
            $campaign->total_recipients
        );

        $this->assertDatabaseHas(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email_subscriber_id' => $activeOne->id,
            ]
        );

        $this->assertDatabaseHas(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email_subscriber_id' => $activeTwo->id,
            ]
        );

        $this->assertDatabaseMissing(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email_subscriber_id' => $unsubscribed->id,
            ]
        );

        $this->assertDatabaseMissing(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email_subscriber_id' => $outsideGroup->id,
            ]
        );

        Queue::assertPushed(
            SendEmailCampaign::class
        );
    }

    public function test_group_scope_requires_a_group(): void
    {
        Queue::fake();

        $campaign = EmailCampaign::create([
            'name' => 'Campaign Without Group',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Test',
            'content' => '<p>Hello.</p>',
            'recipient_scope' => 'group',
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $this->expectException(
            \Illuminate\Validation\ValidationException::class
        );

        app(
            EmailCampaignService::class
        )->queueCampaign($campaign);
    }
}