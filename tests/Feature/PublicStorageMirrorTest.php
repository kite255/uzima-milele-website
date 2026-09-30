<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicStorageMirrorTest extends TestCase
{
    public function test_public_disk_uses_web_visible_storage_when_configured(): void
    {
        $config = file_get_contents(config_path('filesystems.php'));

        $this->assertStringContainsString(
            "'root' => env('PUBLIC_WEB_STORAGE_PATH', storage_path('app/public'))",
            $config
        );
    }

    public function test_public_disk_falls_back_to_laravel_storage_locally(): void
    {
        $config = file_get_contents(config_path('filesystems.php'));

        $this->assertStringContainsString(
            "env('PUBLIC_WEB_STORAGE_PATH', storage_path('app/public'))",
            $config
        );
    }

    public function test_public_disk_url_remains_under_storage(): void
    {
        $this->assertSame(
            rtrim((string) config('app.url'), '/').'/storage',
            config('filesystems.disks.public.url')
        );
    }
}
