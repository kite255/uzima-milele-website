<?php

namespace Tests\Feature;

use App\Filament\Pages\OverdueStudents;
use App\Filament\Resources\QuizResource\RelationManagers\QuestionsRelationManager;
use App\Filament\Resources\QuizResultResource;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonTopic;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorStudentOperationsAccessTest extends TestCase
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
            'description' => 'Instructor operations access test.',
            'is_published' => true,
        ], $attributes));
    }

    private function createModule(
        Lesson $lesson,
        string $title
    ): Module {
        return Module::query()->create([
            'lesson_id' => $lesson->id,
            'title' => $title,
            'description' => 'Test module.',
            'order' => 1,
            'is_published' => true,
        ]);
    }

    private function createTopic(
        Module $module,
        string $title
    ): LessonTopic {
        return LessonTopic::query()->create([
            'module_id' => $module->id,
            'title' => $title,
            'slug' => str($title)->slug() . '-' . uniqid(),
            'content' => 'Test topic content.',
            'order' => 1,
            'is_published' => true,
        ]);
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
            'enrolled_at' => now()->subDays(12),
        ]);
    }

    private function createFinalQuiz(
        Lesson $lesson,
        string $title
    ): Quiz {
        return Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'title' => $title,
            'quiz_type' => 'kujipima',
            'pass_mark' => 70,
            'is_required' => false,
            'is_published' => true,
        ]);
    }

    private function createQuizResult(
        Quiz $quiz,
        User $student,
        int $score = 80
    ): QuizResult {
        return QuizResult::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'user_name' => $student->name,
            'score' => $score,
            'correct' => 8,
            'total' => 10,
            'passed' => $score >= 70,
        ]);
    }

    public function test_follow_up_instructor_sees_only_assigned_students_quiz_results(): void
    {
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $johnStudent = $this->createStudent('John Student');
        $maryStudent = $this->createStudent('Mary Student');

        $lesson = $this->createLesson('Shared Quiz Course');

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $this->createEnrollment(
            $lesson,
            $johnStudent,
            $john
        );

        $this->createEnrollment(
            $lesson,
            $maryStudent,
            $mary
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Shared Final Quiz'
        );

        $johnResult = $this->createQuizResult(
            $quiz,
            $johnStudent
        );

        $maryResult = $this->createQuizResult(
            $quiz,
            $maryStudent
        );

        $this->actingAs($john);

        $query = QuizResultResource::getEloquentQuery();

        $this->assertTrue(
            (clone $query)
                ->whereKey($johnResult->id)
                ->exists()
        );

        $this->assertFalse(
            (clone $query)
                ->whereKey($maryResult->id)
                ->exists()
        );
    }

    public function test_lead_instructor_sees_all_course_quiz_results(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $studentOne = $this->createStudent('Student One');
        $studentTwo = $this->createStudent('Student Two');

        $lesson = $this->createLesson(
            'Lead Quiz Course',
            [
                'lead_instructor_id' => $lead->id,
            ]
        );

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $this->createEnrollment(
            $lesson,
            $studentOne,
            $john
        );

        $this->createEnrollment(
            $lesson,
            $studentTwo,
            $mary
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Lead Final Quiz'
        );

        $resultOne = $this->createQuizResult(
            $quiz,
            $studentOne
        );

        $resultTwo = $this->createQuizResult(
            $quiz,
            $studentTwo
        );

        $this->actingAs($lead);

        $query = QuizResultResource::getEloquentQuery();

        $this->assertTrue(
            (clone $query)
                ->whereKey($resultOne->id)
                ->exists()
        );

        $this->assertTrue(
            (clone $query)
                ->whereKey($resultTwo->id)
                ->exists()
        );
    }

    public function test_follow_up_instructor_overdue_page_shows_only_assigned_students(): void
    {
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $johnStudent = $this->createStudent('John Student');
        $maryStudent = $this->createStudent('Mary Student');

        $lesson = $this->createLesson(
            'Overdue Shared Course'
        );

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $module = $this->createModule(
            $lesson,
            'Incomplete Module'
        );

        $this->createTopic(
            $module,
            'Incomplete Topic'
        );

        $johnEnrollment = $this->createEnrollment(
            $lesson,
            $johnStudent,
            $john
        );

        $maryEnrollment = $this->createEnrollment(
            $lesson,
            $maryStudent,
            $mary
        );

        $this->actingAs($john);

        $page = app(OverdueStudents::class);
        $page->loadRows();

        $enrollmentIds = $page->rows
            ->pluck('enrollment_id');

        $this->assertTrue(
            $enrollmentIds->contains(
                $johnEnrollment->id
            )
        );

        $this->assertFalse(
            $enrollmentIds->contains(
                $maryEnrollment->id
            )
        );
    }

    public function test_lead_instructor_overdue_page_shows_all_course_students(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $studentOne = $this->createStudent('Student One');
        $studentTwo = $this->createStudent('Student Two');

        $lesson = $this->createLesson(
            'Lead Overdue Course',
            [
                'lead_instructor_id' => $lead->id,
            ]
        );

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $module = $this->createModule(
            $lesson,
            'Lead Incomplete Module'
        );

        $this->createTopic(
            $module,
            'Lead Incomplete Topic'
        );

        $enrollmentOne = $this->createEnrollment(
            $lesson,
            $studentOne,
            $john
        );

        $enrollmentTwo = $this->createEnrollment(
            $lesson,
            $studentTwo,
            $mary
        );

        $this->actingAs($lead);

        $page = app(OverdueStudents::class);
        $page->loadRows();

        $enrollmentIds = $page->rows
            ->pluck('enrollment_id');

        $this->assertTrue(
            $enrollmentIds->contains(
                $enrollmentOne->id
            )
        );

        $this->assertTrue(
            $enrollmentIds->contains(
                $enrollmentTwo->id
            )
        );
    }

    public function test_follow_up_instructor_can_manage_quiz_questions_for_team_course(): void
    {
        $instructor = $this->createInstructor(
            'Follow Up Instructor'
        );

        $lesson = $this->createLesson(
            'Follow Up Quiz Questions'
        );

        $lesson->followUpInstructors()
            ->attach($instructor->id);

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Follow Up Quiz'
        );

        $this->actingAs($instructor);

        $this->assertTrue(
            QuestionsRelationManager::canViewForRecord(
                $quiz,
                ''
            )
        );
    }

    public function test_lead_instructor_can_manage_quiz_questions_for_course(): void
    {
        $lead = $this->createInstructor('Lead');

        $lesson = $this->createLesson(
            'Lead Quiz Questions',
            [
                'lead_instructor_id' => $lead->id,
            ]
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Lead Quiz'
        );

        $this->actingAs($lead);

        $this->assertTrue(
            QuestionsRelationManager::canViewForRecord(
                $quiz,
                ''
            )
        );
    }

    public function test_unrelated_instructor_cannot_manage_quiz_questions(): void
    {
        $instructor = $this->createInstructor(
            'Unrelated Instructor'
        );

        $lesson = $this->createLesson(
            'Other Quiz Questions'
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Other Quiz'
        );

        $this->actingAs($instructor);

        $this->assertFalse(
            QuestionsRelationManager::canViewForRecord(
                $quiz,
                ''
            )
        );
    }

    public function test_legacy_instructor_operations_remain_available(): void
    {
        $instructor = $this->createInstructor(
            'Legacy Instructor'
        );

        $student = $this->createStudent(
            'Legacy Student'
        );

        $lesson = $this->createLesson(
            'Legacy Operations Course',
            [
                'instructor_id' => $instructor->id,
            ]
        );

        $module = $this->createModule(
            $lesson,
            'Legacy Module'
        );

        $this->createTopic(
            $module,
            'Legacy Topic'
        );

        $this->createEnrollment(
            $lesson,
            $student
        );

        $quiz = $this->createFinalQuiz(
            $lesson,
            'Legacy Quiz'
        );

        $result = $this->createQuizResult(
            $quiz,
            $student
        );

        $this->actingAs($instructor);

        $this->assertTrue(
            QuizResultResource::getEloquentQuery()
                ->whereKey($result->id)
                ->exists()
        );

        $this->assertTrue(
            QuestionsRelationManager::canViewForRecord(
                $quiz,
                ''
            )
        );

        $page = app(OverdueStudents::class);
        $page->loadRows();

        $this->assertTrue(
            $page->rows
                ->pluck('user_id')
                ->contains($student->id)
        );
    }
}