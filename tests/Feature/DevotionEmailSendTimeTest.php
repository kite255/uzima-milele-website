<?php

namespace Tests\Feature;

use App\Models\Devotion;
use App\Models\EmailSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevotionEmailSendTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_devotion_uses_global_default_email_send_time_when_no_override_exists(): void
    {
        $settings = EmailSetting::current();

        $settings->update([
            'default_devotion_send_time' =>
                '06:00',
        ]);

        $devotion = new Devotion();

        $this->assertSame(
            '06:00',
            $devotion->effectiveEmailSendTime()
        );
    }

    public function test_devotion_can_override_global_default_email_send_time(): void
    {
        $settings = EmailSetting::current();

        $settings->update([
            'default_devotion_send_time' =>
                '06:00',
        ]);

        $devotion = new Devotion();

        $devotion->email_send_time =
            '07:30';

        $this->assertSame(
            '07:30',
            $devotion->effectiveEmailSendTime()
        );
    }
}