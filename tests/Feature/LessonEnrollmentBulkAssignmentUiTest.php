<?php

namespace Tests\Feature;

use App\Filament\Resources\LessonEnrollmentResource;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonEnrollmentBulkAssignmentUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_bulk_assign_students_to_instructor_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $lesson = Lesson::query()->create([
            'title' => 'Bulk Assignment UI Course',
            'slug' => 'bulk-assignment-ui-course',
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
            ->assertSeeText('Assign Students to Instructor');
    }

    public function test_bulk_assignment_options_include_instructor_eligible_for_selected_students(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'name' => 'KISJA JAMES MOLYA',
        ]);

        $studentOne = User::factory()->create(['role' => 'student']);
        $studentTwo = User::factory()->create(['role' => 'student']);

        $lesson = Lesson::query()->create([
            'title' => 'Masomo ya Msingi ya Biblia',
            'slug' => 'masomo-ya-msingi-ya-biblia',
            'description' => 'Course description',
            'is_published' => true,
            'lead_can_receive_students' => false,
        ]);

        $lesson->followUpInstructors()->attach($instructor->id);

        $enrollmentOne = LessonEnrollment::query()->create([
            'user_id' => $studentOne->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $enrollmentTwo = LessonEnrollment::query()->create([
            'user_id' => $studentTwo->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $options = LessonEnrollmentResource::eligibleInstructorOptionsForEnrollments(
            collect([$enrollmentOne, $enrollmentTwo])
        );

        $this->assertArrayHasKey($instructor->id, $options);
        $this->assertSame('KISJA JAMES MOLYA', $options[$instructor->id]);
    }
}
