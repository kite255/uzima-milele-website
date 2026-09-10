<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorFollowUpFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_instructor_sees_record_follow_up_form(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Follow-up Form Test Lesson',
            'slug' => 'follow-up-form-test-lesson',
            'description' => 'Test lesson.',
            'content' => 'Test content.',
            'is_published' => true,
        ]);

        $lesson->followUpInstructors()->attach(
            $instructor->id
        );

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor->id,
            'instructor_assigned_at' => now(),
            'enrolled_at' => now(),
        ]);

        $this->actingAs($instructor)
            ->get(route('instructor.students.show', $enrollment))
            ->assertOk()
            ->assertSee('Record Follow-up')
            ->assertSee('Contact Method')
            ->assertSee('Outcome')
            ->assertSee('Follow-up Status')
            ->assertSee('Next Follow-up')
            ->assertSee(
                route(
                    'instructor.students.follow-ups.store',
                    $enrollment
                ),
                false
            );
    }
}