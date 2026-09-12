<?php

namespace Tests\Feature;

use App\Models\EmailSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_returns_default_email_settings(): void
    {
        $settings = EmailSetting::current();

        $this->assertTrue(
            $settings->auto_schedule_devotions
        );

        $this->assertSame(
            '06:00',
            $settings->default_devotion_send_time
        );

        $this->assertSame(
            'subscribed',
            $settings->default_recipient_scope
        );

        $this->assertNull(
            $settings->email_subscriber_group_id
        );
    }

    public function test_current_reuses_the_same_settings_record(): void
    {
        $first = EmailSetting::current();
        $second = EmailSetting::current();

        $this->assertSame(
            $first->id,
            $second->id
        );

        $this->assertSame(
            1,
            EmailSetting::query()->count()
        );
    }
}