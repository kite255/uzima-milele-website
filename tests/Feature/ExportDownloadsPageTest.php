<?php

namespace Tests\Feature;

use App\Filament\Exports\LessonEnrollmentExporter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExportDownloadsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_completed_exports_with_csv_and_xlsx_download_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $exportId = DB::table('exports')->insertGetId([
            'completed_at' => now(),
            'file_disk' => 'local',
            'file_name' => 'export-1-lesson-enrollments',
            'exporter' => LessonEnrollmentExporter::class,
            'processed_rows' => 24,
            'total_rows' => 24,
            'successful_rows' => 24,
            'user_id' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/export-downloads');

        $response
            ->assertOk()
            ->assertSeeText('Export Downloads')
            ->assertSeeText('export-1-lesson-enrollments')
            ->assertSeeText('Download CSV')
            ->assertSeeText('Download XLSX')
            ->assertSee("/filament/exports/{$exportId}/download?format=csv", false)
            ->assertSee("/filament/exports/{$exportId}/download?format=xlsx", false);
    }
}
