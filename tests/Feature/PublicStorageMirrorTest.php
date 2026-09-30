<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PublicStorageMirrorTest extends TestCase
{
    private string $source;
    private string $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->source = storage_path('app/public');
        $this->destination = storage_path('framework/testing/public-web-storage');

        File::deleteDirectory($this->destination);
        File::ensureDirectoryExists($this->source.'/devotions');
        File::ensureDirectoryExists($this->source.'/lessons/covers');
        File::ensureDirectoryExists($this->source.'/livewire-tmp');

        config(['filesystems.public_web_root' => $this->destination]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->destination);
        File::delete($this->source.'/devotions/sync-test.jpg');
        File::delete($this->source.'/lessons/covers/sync-test.jpg');
        File::delete($this->source.'/livewire-tmp/sync-test.tmp');

        parent::tearDown();
    }

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

    public function test_sync_public_copies_uploaded_files_and_skips_livewire_temporary_files(): void
    {
        File::put($this->source.'/devotions/sync-test.jpg', 'devotion');
        File::put($this->source.'/lessons/covers/sync-test.jpg', 'lesson');
        File::put($this->source.'/livewire-tmp/sync-test.tmp', 'temporary');

        $this->artisan('storage:sync-public')->assertSuccessful();

        $this->assertFileExists($this->destination.'/devotions/sync-test.jpg');
        $this->assertFileExists($this->destination.'/lessons/covers/sync-test.jpg');
        $this->assertFileDoesNotExist($this->destination.'/livewire-tmp/sync-test.tmp');
    }

    public function test_sync_public_does_not_delete_existing_public_files(): void
    {
        File::ensureDirectoryExists($this->destination.'/devotions');
        File::put($this->destination.'/devotions/existing.jpg', 'keep-me');
        File::put($this->source.'/devotions/sync-test.jpg', 'new-file');

        $this->artisan('storage:sync-public')->assertSuccessful();

        $this->assertFileExists($this->destination.'/devotions/existing.jpg');
        $this->assertSame('keep-me', File::get($this->destination.'/devotions/existing.jpg'));
        $this->assertFileExists($this->destination.'/devotions/sync-test.jpg');
    }
}
