<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailSubscriberResource;
use App\Models\EmailSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailSubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_email_subscriber_resource(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this
            ->actingAs($admin)
            ->get(
                EmailSubscriberResource::getUrl('index')
            )
            ->assertSuccessful();
    }

    public function test_subscribed_scope_only_returns_active_subscribers(): void
    {
        EmailSubscriber::create([
            'name' => 'Active Subscriber',
            'email' => 'active@example.com',
            'status' => 'subscribed',
        ]);

        EmailSubscriber::create([
            'name' => 'Inactive Subscriber',
            'email' => 'inactive@example.com',
            'status' => 'unsubscribed',
        ]);

        $this->assertSame(
            1,
            EmailSubscriber::subscribed()->count()
        );
    }
}