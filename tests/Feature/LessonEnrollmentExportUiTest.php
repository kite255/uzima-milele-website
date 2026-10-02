<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonEnrollmentExportUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_current_view_and_selected_export_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $lesson = Lesson::query()->create([
            'title' => 'Exportable Lesson',
            'slug' => 'exportable-lesson',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/lesson-enrollments')
            ->assertOk()
            ->assertSeeText('Export Current View')
            ->assertSeeText('Export Selected');
    }
}
