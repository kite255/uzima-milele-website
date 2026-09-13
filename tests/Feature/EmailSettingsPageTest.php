<?php

namespace Tests\Feature;

use App\Filament\Pages\EmailSettings;
use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailSubscriberGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmailSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_email_settings_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(
            EmailSettings::class
        )
            ->assertSuccessful();
    }

    public function test_non_admin_cannot_access_email_settings_page(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($user);

        $this->assertFalse(
            EmailSettings::canAccess()
        );
    }

    public function test_admin_can_save_default_devotion_email_settings(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $group = EmailSubscriberGroup::query()->create([
            'name' => 'Morning Devotion',
        ]);

        Livewire::test(
            EmailSettings::class
        )
            ->fillForm([
                'auto_schedule_devotions' =>
                    true,

                'default_devotion_send_time' =>
                    '05:30',

                'default_recipient_scope' =>
                    EmailCampaign::RECIPIENT_SCOPE_GROUP,

                'email_subscriber_group_id' =>
                    $group->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = EmailSetting::current();

        $this->assertTrue(
            $settings->auto_schedule_devotions
        );

        $this->assertSame(
            '05:30',
            $settings->default_devotion_send_time
        );

        $this->assertSame(
            EmailCampaign::RECIPIENT_SCOPE_GROUP,
            $settings->default_recipient_scope
        );

        $this->assertSame(
            $group->id,
            $settings->email_subscriber_group_id
        );
    }

    public function test_admin_can_see_public_subscription_page_link(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(
            EmailSettings::class
        )
            ->assertSee(
                route('subscriptions.create')
            )
            ->assertSee(
                'Fungua Ukurasa'
            )
            ->assertSee(
                'Nakili Kiungo'
            );
    }
}