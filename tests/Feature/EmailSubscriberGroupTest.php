<?php

namespace Tests\Feature;

use App\Models\EmailSubscriber;
use App\Models\EmailSubscriberGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailSubscriberGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_can_have_multiple_subscribers(): void
    {
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

        $group = EmailSubscriberGroup::create([
            'name' => 'Morning Devotion',
            'description' => 'Morning devotion subscribers.',
        ]);

        $group->subscribers()->sync([
            $subscriberOne->id,
            $subscriberTwo->id,
        ]);

        $this->assertCount(
            2,
            $group->subscribers
        );

        $this->assertTrue(
            $group->subscribers->contains($subscriberOne)
        );

        $this->assertTrue(
            $group->subscribers->contains($subscriberTwo)
        );
    }

    public function test_subscriber_can_belong_to_multiple_groups(): void
    {
        $subscriber = EmailSubscriber::create([
            'name' => 'Subscriber One',
            'email' => 'one@example.test',
            'status' => 'subscribed',
        ]);

        $groupOne = EmailSubscriberGroup::create([
            'name' => 'Morning Devotion',
        ]);

        $groupTwo = EmailSubscriberGroup::create([
            'name' => 'Leaders',
        ]);

        $groupOne->subscribers()->attach(
            $subscriber->id
        );

        $groupTwo->subscribers()->attach(
            $subscriber->id
        );

        $subscriber->refresh();

        $this->assertCount(
            2,
            $subscriber->groups
        );
    }
}