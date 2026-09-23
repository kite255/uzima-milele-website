<?php

namespace Tests\Feature;

use App\Models\EmailSubscriber;
use App\Models\EmailSuppression;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEmailSuppressionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_unsubscribe_creates_unsubscribe_suppression(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Public Subscriber',
            'email' => 'public@example.com',
            'status' => 'subscribed',
        ]);

        $this->get(
            route('email-subscribers.unsubscribe', $subscriber->unsubscribe_token)
        )->assertOk();

        $subscriber->refresh();

        $this->assertSame('unsubscribed', $subscriber->status);
        $this->assertNotNull($subscriber->unsubscribed_at);

        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'public@example.com',
            'reason' => EmailSuppression::REASON_UNSUBSCRIBED,
            'source' => 'public_unsubscribe',
        ]);
    }

    public function test_public_resubscribe_removes_only_unsubscribe_suppression(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Returning Subscriber',
            'email' => 'return@example.com',
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        EmailSuppression::query()->create([
            'email' => 'return@example.com',
            'reason' => EmailSuppression::REASON_UNSUBSCRIBED,
            'source' => 'public_unsubscribe',
        ]);

        $this->post(route('email-subscribers.store'), [
            'name' => 'Returning Subscriber',
            'email' => 'return@example.com',
            'consent' => '1',
        ])->assertSessionHas('subscription_success');

        $subscriber->refresh();

        $this->assertSame('subscribed', $subscriber->status);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertDatabaseMissing('email_suppressions', [
            'email' => 'return@example.com',
            'reason' => EmailSuppression::REASON_UNSUBSCRIBED,
        ]);
    }

    public function test_public_resubscribe_does_not_remove_manual_suppression(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Blocked Subscriber',
            'email' => 'blocked@example.com',
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        EmailSuppression::query()->create([
            'email' => 'blocked@example.com',
            'reason' => EmailSuppression::REASON_MANUAL,
            'source' => 'admin',
        ]);

        $this->post(route('email-subscribers.store'), [
            'name' => 'Blocked Subscriber',
            'email' => 'blocked@example.com',
            'consent' => '1',
        ])->assertSessionHas('subscription_success');

        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'blocked@example.com',
            'reason' => EmailSuppression::REASON_MANUAL,
            'source' => 'admin',
        ]);
    }

    public function test_preferences_unsubscribe_creates_suppression_and_resubscribe_removes_it(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Preference Subscriber',
            'email' => 'preferences@example.com',
            'status' => 'subscribed',
            'language' => 'sw',
        ]);

        $route = route(
            'email-subscribers.preferences.update',
            $subscriber->unsubscribe_token
        );

        $this->put($route, [
            'name' => 'Preference Subscriber',
            'email' => 'preferences@example.com',
            'language' => 'sw',
            'receive_emails' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'preferences@example.com',
            'reason' => EmailSuppression::REASON_UNSUBSCRIBED,
        ]);

        $this->put($route, [
            'name' => 'Preference Subscriber',
            'email' => 'preferences@example.com',
            'language' => 'sw',
            'receive_emails' => '1',
        ])->assertRedirect();

        $this->assertDatabaseMissing('email_suppressions', [
            'email' => 'preferences@example.com',
            'reason' => EmailSuppression::REASON_UNSUBSCRIBED,
        ]);
    }
}
