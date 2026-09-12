<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailSubscriber;
use App\Models\EmailSubscriberGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEmailSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_subscription_page_is_available(): void
    {
        $this->get(
            route('subscriptions.create')
        )
            ->assertOk()
            ->assertSee(
                'Jiandikishe Kupokea Tafakari'
            );
    }

    public function test_subscription_requires_consent(): void
    {
        $response = $this->from(
            route('subscriptions.create')
        )->post(
            route('email-subscribers.store'),
            [
                'name' =>
                    'Kitenken Lucas',

                'email' =>
                    'kitenken@example.com',

                'phone' =>
                    null,
            ]
        );

        $response->assertSessionHasErrors(
            'consent'
        );

        $this->assertDatabaseMissing(
            'email_subscribers',
            [
                'email' =>
                    'kitenken@example.com',
            ]
        );
    }

    public function test_new_subscriber_is_added_to_default_devotion_group(): void
    {
        $group = EmailSubscriberGroup::query()
            ->create([
                'name' =>
                    'Morning Devotion',
            ]);

        EmailSetting::current()->update([
            'auto_schedule_devotions' =>
                true,

            'default_devotion_send_time' =>
                '06:00',

            'default_recipient_scope' =>
                EmailCampaign::RECIPIENT_SCOPE_GROUP,

            'email_subscriber_group_id' =>
                $group->id,
        ]);

        $this->post(
            route('email-subscribers.store'),
            [
                'name' =>
                    'Kitenken Lucas',

                'email' =>
                    'kitenken@example.com',

                'phone' =>
                    '+255700000000',

                'consent' =>
                    '1',
            ]
        )
            ->assertSessionHas(
                'subscription_success'
            );

        $subscriber = EmailSubscriber::query()
            ->where(
                'email',
                'kitenken@example.com'
            )
            ->firstOrFail();

        $this->assertSame(
            'subscribed',
            $subscriber->status
        );

        $this->assertTrue(
            $subscriber
                ->groups()
                ->where(
                    'email_subscriber_groups.id',
                    $group->id
                )
                ->exists()
        );
    }

    public function test_existing_unsubscribed_subscriber_is_reactivated_without_duplicate(): void
    {
        $subscriber = EmailSubscriber::query()
            ->create([
                'name' =>
                    'Kitenken Lucas',

                'email' =>
                    'kitenken@example.com',

                'status' =>
                    'unsubscribed',

                'unsubscribed_at' =>
                    now(),
            ]);

        $this->post(
            route('email-subscribers.store'),
            [
                'name' =>
                    'Kitenken Lucas Ryoba',

                'email' =>
                    'kitenken@example.com',

                'phone' =>
                    '+255700000000',

                'consent' =>
                    '1',
            ]
        )
            ->assertSessionHas(
                'subscription_success'
            );

        $subscriber->refresh();

        $this->assertSame(
            'subscribed',
            $subscriber->status
        );

        $this->assertNull(
            $subscriber->unsubscribed_at
        );

        $this->assertSame(
            1,
            EmailSubscriber::query()
                ->where(
                    'email',
                    'kitenken@example.com'
                )
                ->count()
        );
    }
}