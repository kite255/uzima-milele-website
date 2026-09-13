<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailSubscriber;
use App\Services\EmailCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignAudienceSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_subscribers_scope_snapshots_all_active_subscribers(): void
    {
        Queue::fake();

        $subscriberOne = EmailSubscriber::create([
            'name' => 'Subscriber One',
            'email' => 'one@example.test',
            'status' => 'subscribed',
        ]);

        $subscriberTwo = EmailSubscriber::create([
            'name' => 'Subscriber Two',
            'email' => 'two@example.test',
            'status' => 'subscribed',
        ]);

        EmailSubscriber::create([
            'name' => 'Unsubscribed User',
            'email' => 'unsubscribed@example.test',
            'status' => 'unsubscribed',
        ]);

        $campaign = EmailCampaign::create([
            'name' => 'All Subscribers Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Test Campaign',
            'content' => '<p>Hello subscribers.</p>',
            'recipient_scope' => 'subscribed',
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
                'email_subscriber_id' => $subscriberOne->id,
                'email' => 'one@example.test',
            ]
        );

        $this->assertDatabaseHas(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email_subscriber_id' => $subscriberTwo->id,
                'email' => 'two@example.test',
            ]
        );

        $this->assertDatabaseMissing(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email' => 'unsubscribed@example.test',
            ]
        );

        Queue::assertPushed(
            SendEmailCampaign::class
        );
    }

    public function test_selected_subscribers_scope_snapshots_only_selected_active_subscribers(): void
    {
        Queue::fake();

        $subscriberOne = EmailSubscriber::create([
            'name' => 'Subscriber One',
            'email' => 'one@example.test',
            'status' => 'subscribed',
        ]);

        $subscriberTwo = EmailSubscriber::create([
            'name' => 'Subscriber Two',
            'email' => 'two@example.test',
            'status' => 'subscribed',
        ]);

        $subscriberThree = EmailSubscriber::create([
            'name' => 'Subscriber Three',
            'email' => 'three@example.test',
            'status' => 'subscribed',
        ]);

        $campaign = EmailCampaign::create([
            'name' => 'Selected Subscribers Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Selected Audience',
            'content' => '<p>Hello selected subscribers.</p>',
            'recipient_scope' => 'selected',
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $campaign->targetSubscribers()->sync([
            $subscriberOne->id,
            $subscriberThree->id,
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
                'email_subscriber_id' => $subscriberOne->id,
            ]
        );

        $this->assertDatabaseHas(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email_subscriber_id' => $subscriberThree->id,
            ]
        );

        $this->assertDatabaseMissing(
            'email_campaign_recipients',
            [
                'email_campaign_id' => $campaign->id,
                'email_subscriber_id' => $subscriberTwo->id,
            ]
        );

        Queue::assertPushed(
            SendEmailCampaign::class
        );
    }
}