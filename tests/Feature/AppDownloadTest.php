<?php

namespace Tests\Feature;

use App\Models\AppRelease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_release_is_not_downloadable(): void
    {
        $this->get('/download/app')->assertOk()->assertSee('Programu haipatikani');
        $this->get('/download/android')->assertNotFound();
    }

    public function test_draft_is_private_and_published_file_is_downloadable(): void
    {
        Storage::fake('local');
        $path = 'app-releases/12345678-1234-1234-1234-123456789abc.apk';
        Storage::disk('local')->put($path, 'test fixture');
        $release = AppRelease::create([
            'version' => '1.1.0', 'apk_path' => $path, 'size_bytes' => 12,
            'sha256' => hash('sha256', 'test fixture'),
        ]);
        $this->get('/download/android')->assertNotFound();
        $release->update(['published_at' => now()]);
        $this->get('/download/app')->assertOk()->assertSee('1.1.0')->assertSee('Pakua kwa Android');
        $this->get('/download/android')->assertOk()->assertDownload('Uzima-Milele-1.1.0.apk');
        $this->assertSame(1, $release->fresh()->downloads);
        $release->update(['published_at' => null]);
        $this->get('/download/android')->assertNotFound();
    }
}
