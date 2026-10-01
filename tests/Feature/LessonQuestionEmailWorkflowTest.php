<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\User;
use App\Notifications\LessonQuestionAskedNotification;
use App\Notifications\LessonQuestionEscalatedNotification;
use App\Notifications\LessonQuestionReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LessonQuestionEmailWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function instructor(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'instructor',
        ]);
    }

    private function student(string $name = 'Student'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'student',
        ]);
    }

    private function lesson(array $attributes = []): Lesson
    {
        return Lesson::query()->create(array_merge([
            'title' => 'Email Workflow Course',
            'slug' => 'email-workflow-course-' . uniqid(),
            'description' => 'Email workflow test.',
            'is_published' => true,
        ], $attributes));
    }

    private function enroll(
        Lesson $lesson,
        User $student,
        ?User $followUp = null
    ): LessonEnrollment {
        return LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUp?->id,
            'instructor_assigned_at' => $followUp ? now() : null,
            'enrolled_at' => now(),
        ]);
    }

    private function ask(Lesson $lesson, User $student): void
    {
        $this->actingAs($student)
            ->post(route('lessons.questions.store', $lesson), [
                'question' => 'Naomba ufafanuzi zaidi kuhusu somo hili.',
            ])
            ->assertSessionHas('success');
    }

    public function test_new_question_is_sent_to_students_assigned_follow_up_instructor(): void
    {
        Notification::fake();

        $followUp = $this->instructor('Follow Up');
        $lead = $this->instructor('Lead');
        $student = $this->student();
        $lesson = $this->lesson([
            'lead_instructor_id' => $lead->id,
        ]);

        $lesson->followUpInstructors()->attach($followUp->id);
        $this->enroll($lesson, $student, $followUp);

        $this->ask($lesson, $student);

        Notification::assertSentTo(
            $followUp,
            LessonQuestionAskedNotification::class
        );
        Notification::assertNotSentTo(
            $lead,
            LessonQuestionAskedNotification::class
        );
    }

    public function test_new_question_falls_back_to_lead_then_legacy_instructor_then_admin(): void
    {
        Notification::fake();

        $lead = $this->instructor('Lead');
        $legacy = $this->instructor('Legacy');
        $admin = User::factory()->create(['role' => 'admin']);

        $leadStudent = $this->student('Lead Student');
        $leadLesson = $this->lesson([
            'lead_instructor_id' => $lead->id,
            'instructor_id' => $legacy->id,
        ]);
        $this->enroll($leadLesson, $leadStudent);
        $this->ask($leadLesson, $leadStudent);
        Notification::assertSentTo($lead, LessonQuestionAskedNotification::class);
        Notification::assertNotSentTo($legacy, LessonQuestionAskedNotification::class);

        Notification::fake();
        $legacyStudent = $this->student('Legacy Student');
        $legacyLesson = $this->lesson(['instructor_id' => $legacy->id]);
        $this->enroll($legacyLesson, $legacyStudent);
        $this->ask($legacyLesson, $legacyStudent);
        Notification::assertSentTo($legacy, LessonQuestionAskedNotification::class);

        Notification::fake();
        $adminStudent = $this->student('Admin Student');
        $adminLesson = $this->lesson();
        $this->enroll($adminLesson, $adminStudent);
        $this->ask($adminLesson, $adminStudent);
        Notification::assertSentTo($admin, LessonQuestionAskedNotification::class);
    }

    public function test_pending_question_is_reminded_at_24_hours_escalated_at_48_and_admin_at_72_once_each(): void
    {
        Notification::fake();

        $followUp = $this->instructor('Follow Up');
        $lead = $this->instructor('Lead');
        $admin = User::factory()->create(['role' => 'admin']);
        $student = $this->student();
        $lesson = $this->lesson([
            'lead_instructor_id' => $lead->id,
        ]);
        $this->enroll($lesson, $student, $followUp);

        $question = LessonQuestion::query()->create([
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'question' => 'Swali ambalo halijajibiwa.',
            'status' => LessonQuestion::STATUS_PENDING,
            'visibility' => LessonQuestion::VISIBILITY_PRIVATE,
            'is_published' => true,
        ]);

        $question->forceFill([
            'created_at' => now()->subHours(25),
            'updated_at' => now()->subHours(25),
        ])->saveQuietly();

        $this->artisan('lessons:send-question-escalations')->assertSuccessful();

        Notification::assertSentTo(
            $followUp,
            LessonQuestionReminderNotification::class
        );

        $question->forceFill([
            'created_at' => now()->subHours(49),
        ])->saveQuietly();

        $this->artisan('lessons:send-question-escalations')->assertSuccessful();

        Notification::assertSentTo(
            $lead,
            LessonQuestionEscalatedNotification::class,
            fn ($notification) => $notification->level === 'lead'
        );

        $question->forceFill([
            'created_at' => now()->subHours(73),
        ])->saveQuietly();

        $this->artisan('lessons:send-question-escalations')->assertSuccessful();
        $this->artisan('lessons:send-question-escalations')->assertSuccessful();

        Notification::assertSentTo(
            $admin,
            LessonQuestionEscalatedNotification::class,
            fn ($notification) => $notification->level === 'admin'
        );

        $question->refresh();

        $this->assertNotNull($question->instructor_reminded_at);
        $this->assertNotNull($question->lead_escalated_at);
        $this->assertNotNull($question->admin_escalated_at);

        Notification::assertSentToTimes(
            $followUp,
            LessonQuestionReminderNotification::class,
            1
        );
    }

    public function test_answered_questions_are_not_escalated(): void
    {
        Notification::fake();

        $followUp = $this->instructor('Follow Up');
        $student = $this->student();
        $lesson = $this->lesson();
        $this->enroll($lesson, $student, $followUp);

        LessonQuestion::query()->create([
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'question' => 'Already answered.',
            'answer' => 'Answered.',
            'answered_by' => $followUp->id,
            'answered_at' => now()->subHours(70),
            'status' => LessonQuestion::STATUS_ANSWERED,
            'visibility' => LessonQuestion::VISIBILITY_PRIVATE,
            'is_published' => true,
            'created_at' => now()->subHours(80),
            'updated_at' => now()->subHours(70),
        ]);

        $this->artisan('lessons:send-question-escalations')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
