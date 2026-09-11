<?php

namespace Tests\Feature;

use App\Filament\Resources\LessonQuestionResource;
use App\Http\Controllers\InstructorQuestionController;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorQuestionAssignmentAccessTest extends TestCase
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

    private function createLesson(
        string $title,
        array $attributes = []
    ): Lesson {
        return Lesson::query()->create(array_merge([
            'title' => $title,
            'slug' => str($title)->slug() . '-' . uniqid(),
            'description' => 'Question access test.',
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

    private function createQuestion(
        Lesson $lesson,
        User $student
    ): LessonQuestion {
        return LessonQuestion::query()->create([
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'question' => 'Naomba ufafanuzi zaidi.',
            'status' => LessonQuestion::STATUS_PENDING,
            'visibility' => LessonQuestion::VISIBILITY_PRIVATE,
            'is_published' => true,
        ]);
    }

    public function test_follow_up_instructor_sees_question_from_assigned_student(): void
    {
        $john = $this->createInstructor('John');
        $student = $this->createStudent('John Student');

        $lesson = $this->createLesson('Question Course');

        $lesson->followUpInstructors()
            ->attach($john->id);

        $this->createEnrollment(
            $lesson,
            $student,
            $john
        );

        $question = $this->createQuestion(
            $lesson,
            $student
        );

        $this->actingAs($john);

        $this->assertTrue(
            LessonQuestionResource::getEloquentQuery()
                ->whereKey($question->id)
                ->exists()
        );

        $response = app(
            InstructorQuestionController::class
        )->show($question);

        $this->assertSame(
            'instructor.questions.show',
            $response->name()
        );
    }

    public function test_follow_up_instructor_cannot_see_question_from_another_instructors_student(): void
    {
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $student = $this->createStudent('Mary Student');

        $lesson = $this->createLesson('Shared Course');

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $this->createEnrollment(
            $lesson,
            $student,
            $mary
        );

        $question = $this->createQuestion(
            $lesson,
            $student
        );

        $this->actingAs($john);

        $this->assertFalse(
            LessonQuestionResource::getEloquentQuery()
                ->whereKey($question->id)
                ->exists()
        );

        $this->expectException(
            \Symfony\Component\HttpKernel\Exception\HttpException::class
        );

        app(
            InstructorQuestionController::class
        )->show($question);
    }

    public function test_lead_instructor_can_see_questions_from_all_course_students(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');

        $student = $this->createStudent('Student');

        $lesson = $this->createLesson(
            'Lead Question Course',
            [
                'lead_instructor_id' => $lead->id,
            ]
        );

        $lesson->followUpInstructors()
            ->attach($john->id);

        $this->createEnrollment(
            $lesson,
            $student,
            $john
        );

        $question = $this->createQuestion(
            $lesson,
            $student
        );

        $this->actingAs($lead);

        $this->assertTrue(
            LessonQuestionResource::getEloquentQuery()
                ->whereKey($question->id)
                ->exists()
        );

        $response = app(
            InstructorQuestionController::class
        )->show($question);

        $this->assertSame(
            'instructor.questions.show',
            $response->name()
        );
    }

    public function test_admin_can_see_any_question(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $student = $this->createStudent('Student');
        $lesson = $this->createLesson('Admin Course');

        $question = $this->createQuestion(
            $lesson,
            $student
        );

        $this->actingAs($admin);

        $this->assertTrue(
            LessonQuestionResource::getEloquentQuery()
                ->whereKey($question->id)
                ->exists()
        );

        $response = app(
            InstructorQuestionController::class
        )->show($question);

        $this->assertSame(
            'instructor.questions.show',
            $response->name()
        );
    }

    public function test_legacy_instructor_access_still_works_for_unmigrated_course(): void
    {
        $instructor = $this->createInstructor(
            'Legacy Instructor'
        );

        $student = $this->createStudent(
            'Legacy Student'
        );

        $lesson = $this->createLesson(
            'Legacy Question Course',
            [
                'instructor_id' => $instructor->id,
            ]
        );

        $question = $this->createQuestion(
            $lesson,
            $student
        );

        $this->actingAs($instructor);

        $this->assertTrue(
            LessonQuestionResource::getEloquentQuery()
                ->whereKey($question->id)
                ->exists()
        );

        $response = app(
            InstructorQuestionController::class
        )->show($question);

        $this->assertSame(
            'instructor.questions.show',
            $response->name()
        );
    }
}