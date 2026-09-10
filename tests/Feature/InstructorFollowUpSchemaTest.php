<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\StudentFollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstructorFollowUpSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_up_tracking_columns_exist_on_lesson_enrollments(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'lesson_enrollments',
                [
                    'follow_up_status',
                    'next_follow_up_at',
                    'last_follow_up_at',
                ]
            )
        );
    }

    public function test_student_follow_ups_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('student_follow_ups')
        );

        $this->assertTrue(
            Schema::hasColumns(
                'student_follow_ups',
                [
                    'id',
                    'lesson_enrollment_id',
                    'instructor_id',
                    'contact_method',
                    'outcome',
                    'note',
                    'next_follow_up_at',
                    'created_at',
                    'updated_at',
                ]
            )
        );
    }

    public function test_enrollment_has_follow_up_history_relationship(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Test Lesson',
            'slug' => 'test-lesson-follow-up',
            'description' => 'Test lesson for instructor follow-up.',
            'content' => 'Test content.',
            'is_published' => true,
        ]);

        $enrollment = LessonEnrollment::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor->id,
        ]);

        $followUp = StudentFollowUp::create([
            'lesson_enrollment_id' => $enrollment->id,
            'instructor_id' => $instructor->id,
            'contact_method' => 'whatsapp',
            'outcome' => 'reached',
            'note' => 'Student was contacted successfully.',
        ]);

        $this->assertTrue(
            $enrollment->fresh()
                ->followUps
                ->contains($followUp)
        );

        $this->assertTrue(
            $followUp->enrollment->is($enrollment)
        );

        $this->assertTrue(
            $followUp->instructor->is($instructor)
        );
    }
}