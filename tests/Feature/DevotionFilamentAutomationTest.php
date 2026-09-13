<?php

namespace Tests\Feature;

use App\Filament\Resources\DevotionResource\Pages\CreateDevotion;
use App\Filament\Resources\DevotionResource\Pages\EditDevotion;
use App\Models\Devotion;
use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailSubscriber;
use App\Models\User;
use App\Services\DevotionEmailAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class DevotionFilamentAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_devotion_in_filament_automatically_schedules_email_campaign(): void
    {
        Queue::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        EmailSubscriber::query()->create([
            'name' => 'Test Subscriber',
            'email' => 'subscriber@example.com',
            'status' => 'subscribed',
            'unsubscribe_token' => hash(
                'sha256',
                'subscriber@example.com'
                . microtime(true)
            ),
            'subscribed_at' => now(),
        ]);

        EmailSetting::current()->update([
            'auto_schedule_devotions' => true,
            'default_devotion_send_time' => '06:00',
            'default_recipient_scope' =>
                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'email_subscriber_group_id' => null,
        ]);

        $date = now()
            ->addDays(2)
            ->toDateString();

        Livewire::test(CreateDevotion::class)
            ->fillForm([
                'title' => 'Tumaini Katika Mungu',
                'slug' => 'tumaini-katika-mungu',
                'content' => '<p>Tumaini katika Mungu kila siku.</p>',
                'published_at' => $date,
                'email_send_time' => '06:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(
            'email_campaigns',
            [
                'type' =>
                    EmailCampaign::TYPE_DEVOTION,

                'status' =>
                    EmailCampaign::STATUS_SCHEDULED,

                'recipient_scope' =>
                    EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            ]
        );

        $campaign = EmailCampaign::query()
            ->where(
                'type',
                EmailCampaign::TYPE_DEVOTION
            )
            ->firstOrFail();

        $this->assertSame(
            $date . ' 06:00',
            $campaign->scheduled_at
                ->format('Y-m-d H:i')
        );

        $this->assertSame(
            1,
            $campaign->total_recipients
        );
    }

    public function test_editing_devotion_in_filament_reschedules_existing_campaign_without_duplicate(): void
    {
        Queue::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        EmailSubscriber::query()->create([
            'name' => 'Test Subscriber',
            'email' => 'subscriber@example.com',
            'status' => 'subscribed',
            'unsubscribe_token' => hash(
                'sha256',
                'subscriber@example.com'
                . microtime(true)
            ),
            'subscribed_at' => now(),
        ]);

        EmailSetting::current()->update([
            'auto_schedule_devotions' => true,
            'default_devotion_send_time' => '06:00',
            'default_recipient_scope' =>
                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
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

        $originalCampaignId =
            $campaign->id;

        $newDate = now()
            ->addDays(5)
            ->toDateString();

        Livewire::test(
            EditDevotion::class,
            [
                'record' =>
                    $devotion->getRouteKey(),
            ]
        )
            ->fillForm([
                'title' =>
                    'Tumaini Katika Mungu Lililohaririwa',

                'slug' =>
                    'tumaini-katika-mungu',

                'published_at' =>
                    $newDate,

                'email_send_time' =>
                    '07:30',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            1,
            EmailCampaign::query()
                ->where(
                    'devotion_id',
                    $devotion->id
                )
                ->count()
        );

        $updatedCampaign =
            EmailCampaign::query()
                ->where(
                    'devotion_id',
                    $devotion->id
                )
                ->firstOrFail();

        $this->assertSame(
            $originalCampaignId,
            $updatedCampaign->id
        );

        $this->assertSame(
            EmailCampaign::STATUS_SCHEDULED,
            $updatedCampaign->status
        );

        $this->assertSame(
            $newDate . ' 07:30',
            $updatedCampaign
                ->scheduled_at
                ->format(
                    'Y-m-d H:i'
                )
        );

        $this->assertSame(
            'Tumaini Katika Mungu Lililohaririwa',
            $updatedCampaign->subject
        );
    }
}