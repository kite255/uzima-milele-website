<?php

namespace Tests\Feature;

use App\Filament\Resources\LessonEnrollmentResource;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonEnrollmentResourceInstructorAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createInstructor(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'instructor',
        ]);
    }

    private function createStudent(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'student',
        ]);
    }

    private function createLesson(array $attributes = []): Lesson
    {
        return Lesson::query()->create(array_merge([
            'title' => 'Instructor Access Course',
            'slug' => 'instructor-access-' . uniqid(),
            'description' => 'Test course.',
            'is_published' => true,
        ], $attributes));
    }

    private function createEnrollment(
        Lesson $lesson,
        User $student,
        ?User $instructor = null
    ): LessonEnrollment {
        return LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor?->id,
            'instructor_assigned_at' => $instructor ? now() : null,
            'enrolled_at' => now(),
        ]);
    }

    public function test_follow_up_instructor_sees_only_students_assigned_to_them(): void
    {
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson = $this->createLesson();

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $johnEnrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent('John Student'),
            $john
        );

        $maryEnrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent('Mary Student'),
            $mary
        );

        $this->actingAs($john);

        $query = LessonEnrollmentResource::getEloquentQuery();

        $this->assertTrue(
            $query->clone()
                ->whereKey($johnEnrollment->id)
                ->exists()
        );

        $this->assertFalse(
            $query->clone()
                ->whereKey($maryEnrollment->id)
                ->exists()
        );
    }

    public function test_lead_instructor_sees_all_students_in_their_course(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson = $this->createLesson([
            'lead_instructor_id' => $lead->id,
        ]);

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $firstEnrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent('Student One'),
            $john
        );

        $secondEnrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent('Student Two'),
            $mary
        );

        $this->actingAs($lead);

        $query = LessonEnrollmentResource::getEloquentQuery();

        $this->assertTrue(
            $query->clone()
                ->whereKey($firstEnrollment->id)
                ->exists()
        );

        $this->assertTrue(
            $query->clone()
                ->whereKey($secondEnrollment->id)
                ->exists()
        );
    }

    public function test_follow_up_instructor_cannot_see_unassigned_student(): void
    {
        $john = $this->createInstructor('John');

        $lesson = $this->createLesson();

        $lesson->followUpInstructors()
            ->attach($john->id);

        $enrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent('Unassigned Student')
        );

        $this->actingAs($john);

        $this->assertFalse(
            LessonEnrollmentResource::getEloquentQuery()
                ->whereKey($enrollment->id)
                ->exists()
        );
    }

    public function test_admin_can_see_all_enrollments(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $lesson = $this->createLesson();

        $enrollment = $this->createEnrollment(
            $lesson,
            $this->createStudent('Student')
        );

        $this->actingAs($admin);

        $this->assertTrue(
            LessonEnrollmentResource::getEloquentQuery()
                ->whereKey($enrollment->id)
                ->exists()
        );
    }
}