<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BrandedNotificationEmailTest extends TestCase
{
    public function test_notification_mail_header_uses_uzima_milele_logo_and_brand(): void
    {
        $path = resource_path('views/vendor/mail/html/header.blade.php');

        $this->assertFileExists($path);

        $contents = File::get($path);

        $this->assertStringContainsString("asset('logo.png')", $contents);
        $this->assertStringContainsString('Uzima Milele', $contents);
        $this->assertStringContainsString('#0083CB', $contents);
        $this->assertStringContainsString('#F4B122', $contents);
    }

    public function test_notification_mail_footer_uses_ministry_identity(): void
    {
        $path = resource_path('views/vendor/mail/html/footer.blade.php');

        $this->assertFileExists($path);

        $contents = File::get($path);

        $this->assertStringContainsString('Uzima Milele Ministry', $contents);
        $this->assertStringContainsString('Kujifunza', $contents);
        $this->assertStringContainsString('Kukua', $contents);
        $this->assertStringContainsString('Kumfuata Kristo', $contents);
    }

    public function test_notification_action_button_uses_uzima_milele_primary_colour(): void
    {
        $path = resource_path('views/vendor/mail/html/button.blade.php');

        $this->assertFileExists($path);

        $contents = File::get($path);

        $this->assertStringContainsString('#0083CB', $contents);
        $this->assertStringContainsString('#0E3D4F', $contents);
    }
}
