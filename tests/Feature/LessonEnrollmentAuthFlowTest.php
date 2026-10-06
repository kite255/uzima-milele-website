<?php

namespace Tests\Feature;

use App\Models\InstructorEnrollmentLink;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LessonEnrollmentAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lesson_login_and_registration_return_to_same_lesson_schedule(): void
    {
        $lesson = $this->publishedLesson('normal-enrollment-flow');

        $returnUrl = route('lessons.show', ['lesson' => $lesson->slug])
            . '#learning-schedule';

        $this->get(route('lessons.show', ['lesson' => $lesson->slug]))
            ->assertOk()
            ->assertSee(
                route('login', ['redirect' => $returnUrl]),
                false
            )
            ->assertSee(
                route('register', ['redirect' => $returnUrl]),
                false
            );
    }

    public function test_email_login_returns_student_to_same_lesson_schedule(): void
    {
        $lesson = $this->publishedLesson('login-return-flow');

        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
        ]);

        $returnUrl = route('lessons.show', ['lesson' => $lesson->slug])
            . '#learning-schedule';

        $this->post(route('login'), [
            'email' => $student->email,
            'password' => 'password',
            'redirect' => $returnUrl,
        ])->assertRedirect($returnUrl);
    }

    public function test_registration_returns_new_student_to_same_lesson_schedule(): void
    {
        $lesson = $this->publishedLesson('registration-return-flow');

        $returnUrl = route('lessons.show', ['lesson' => $lesson->slug])
            . '#learning-schedule';

        $this->post(route('register'), [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'phone' => '0712345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'redirect' => $returnUrl,
        ])->assertRedirect($returnUrl);

        $this->assertAuthenticated();
    }

    public function test_instructor_referral_survives_login_and_assigns_referring_instructor(): void
    {
        Notification::fake();

        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'referred@example.com',
            'password' => Hash::make('password'),
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->publishedLesson('referred-login-flow');
        $lesson->followUpInstructors()->attach($instructor->id);

        $link = InstructorEnrollmentLink::query()->create([
            'lesson_id' => $lesson->id,
            'instructor_id' => $instructor->id,
            'code' => 'FLOW99',
        ]);

        $this->get(route('lessons.instructor-join', ['code' => $link->code]))
            ->assertOk();

        $returnUrl = route('lessons.show', ['lesson' => $lesson->slug])
            . '#learning-schedule';

        $this->post(route('login'), [
            'email' => $student->email,
            'password' => 'password',
            'redirect' => $returnUrl,
        ])->assertRedirect($returnUrl);

        $this->post(
            route('lessons.enroll', ['lesson' => $lesson->slug]),
            ['study_pace' => Lesson::PACE_REGULAR]
        )->assertRedirect(
            route('lessons.learn', ['lesson' => $lesson->slug])
        );

        $enrollment = LessonEnrollment::query()
            ->where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $this->assertSame(
            $instructor->id,
            $enrollment->follow_up_instructor_id
        );
    }

    private function publishedLesson(string $slug): Lesson
    {
        return Lesson::query()->create([
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'description' => 'Lesson used to test seamless enrollment authentication.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);
    }
}
