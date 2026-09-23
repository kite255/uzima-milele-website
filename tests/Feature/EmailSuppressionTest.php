<?php

namespace Tests\Feature;

use App\Models\EmailSubscriber;
use App\Models\EmailSuppression;
use App\Services\Email\CampaignSuppressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailSuppressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsubscribed_subscriber_is_not_eligible_to_receive_campaigns(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Suppressed Subscriber',
            'email' => 'person@example.com',
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        $service = app(CampaignSuppressionService::class);

        $service->suppress(
            $subscriber->email,
            EmailSuppression::REASON_UNSUBSCRIBED,
            'public_unsubscribe'
        );

        $this->assertTrue($service->isSuppressed('PERSON@EXAMPLE.COM'));
        $this->assertFalse($service->canReceive($subscriber));
    }

    public function test_subscribed_valid_unsuppressed_subscriber_can_receive_campaigns(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Active Subscriber',
            'email' => 'active@example.com',
            'status' => 'subscribed',
        ]);

        $this->assertTrue(
            app(CampaignSuppressionService::class)->canReceive($subscriber)
        );
    }

    public function test_invalid_email_address_cannot_receive_campaigns(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Invalid Subscriber',
            'email' => 'not-an-email',
            'status' => 'subscribed',
        ]);

        $this->assertFalse(
            app(CampaignSuppressionService::class)->canReceive($subscriber)
        );
    }

    public function test_remove_unsubscribe_suppression_only_removes_unsubscribe_reason(): void
    {
        $service = app(CampaignSuppressionService::class);

        $service->suppress(
            'unsubscribe@example.com',
            EmailSuppression::REASON_UNSUBSCRIBED,
            'public_unsubscribe'
        );

        $service->suppress(
            'manual@example.com',
            EmailSuppression::REASON_MANUAL,
            'admin'
        );

        $service->removeUnsubscribeSuppression('unsubscribe@example.com');
        $service->removeUnsubscribeSuppression('manual@example.com');

        $this->assertFalse($service->isSuppressed('unsubscribe@example.com'));
        $this->assertTrue($service->isSuppressed('manual@example.com'));
    }

    public function test_suppression_upsert_normalizes_email_and_updates_reason(): void
    {
        $service = app(CampaignSuppressionService::class);

        $service->suppress(
            ' Person@Example.COM ',
            EmailSuppression::REASON_REPEATED_FAILURE,
            'campaign_send',
            'Three consecutive failures'
        );

        $service->suppress(
            'person@example.com',
            EmailSuppression::REASON_MANUAL,
            'admin'
        );

        $this->assertSame(1, EmailSuppression::query()->count());

        $suppression = EmailSuppression::query()->firstOrFail();

        $this->assertSame('person@example.com', $suppression->email);
        $this->assertSame(EmailSuppression::REASON_MANUAL, $suppression->reason);
        $this->assertSame('admin', $suppression->source);
    }
}
