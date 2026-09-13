<?php

namespace Tests\Feature;

use App\Models\EmailSubscriber;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailSubscriberUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscriber_email_is_always_stored_in_lowercase(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => 'Test Subscriber',
            'email' => 'Test.User@Example.COM',
        ]);

        $this->assertSame(
            'test.user@example.com',
            $subscriber->fresh()->email
        );
    }

    public function test_database_does_not_allow_duplicate_subscriber_email(): void
    {
        EmailSubscriber::query()->create([
            'name' => 'First Subscriber',
            'email' => 'duplicate@example.com',
        ]);

        $this->expectException(QueryException::class);

        EmailSubscriber::query()->create([
            'name' => 'Second Subscriber',
            'email' => 'duplicate@example.com',
        ]);
    }
}