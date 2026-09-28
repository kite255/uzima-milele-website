<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicStorageMirrorTest extends TestCase
{
    public function test_public_disk_uses_laravel_storage_directory(): void
    {
        $this->assertSame(
            storage_path('app/public'),
            config('filesystems.disks.public.root')
        );
    }

    public function test_public_web_storage_path_can_be_configured(): void
    {
        $config = file_get_contents(config_path('filesystems.php'));

        $this->assertStringContainsString("'public_web_root' => env('PUBLIC_WEB_STORAGE_PATH')", $config);
    }

    public function test_sync_public_command_is_registered(): void
    {
        $this->artisan('storage:sync-public', ['--dry-run' => true])
            ->assertSuccessful();
    }
}
