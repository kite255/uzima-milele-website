<?php

namespace Tests\Feature;

use App\Models\InstructorEnrollmentLink;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use App\Services\InstructorEnrollmentReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InstructorEnrollmentLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_creates_short_reusable_link_for_eligible_instructor(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->publishedLesson('short-link-lesson');
        $lesson->followUpInstructors()->attach($instructor->id);

        $service = app(InstructorEnrollmentReferralService::class);

        $firstUrl = $service->shortUrl($lesson, $instructor);
        $secondUrl = $service->shortUrl($lesson, $instructor);

        $link = InstructorEnrollmentLink::query()->firstOrFail();

        $this->assertSame($firstUrl, $secondUrl);
        $this->assertStringEndsWith('/join/' . $link->code, $firstUrl);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $link->code);
        $this->assertSame($lesson->id, $link->lesson_id);
        $this->assertSame($instructor->id, $link->instructor_id);
        $this->assertSame(1, InstructorEnrollmentLink::query()->count());
    }

    public function test_short_code_landing_page_shows_lesson_and_instructor_name(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'name' => 'John Instructor',
        ]);

        $lesson = $this->publishedLesson('landing-page-lesson');
        $lesson->followUpInstructors()->attach($instructor->id);

        $link = InstructorEnrollmentLink::query()->create([
            'lesson_id' => $lesson->id,
            'instructor_id' => $instructor->id,
            'code' => 'LAND99',
        ]);

        $this
            ->get(route('lessons.instructor-join', ['code' => $link->code]))
            ->assertOk()
            ->assertViewIs('instructor.enrollment-link-join')
            ->assertSee($lesson->title)
            ->assertSee($instructor->name)
            ->assertSee(route('lessons.show', ['lesson' => $lesson->slug]));
    }

    public function test_student_joining_through_short_code_is_assigned_to_that_instructor(): void
    {
        Notification::fake();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->publishedLesson('instructor-link-lesson');
        $lesson->followUpInstructors()->attach($instructor->id);

        $link = InstructorEnrollmentLink::query()->create([
            'lesson_id' => $lesson->id,
            'instructor_id' => $instructor->id,
            'code' => 'AB7K2Q',
        ]);

        $this
            ->get(route('lessons.instructor-join', ['code' => $link->code]))
            ->assertOk()
            ->assertViewIs('instructor.enrollment-link-join')
            ->assertSee($instructor->name);

        $this
            ->actingAs($student)
            ->post(
                route('lessons.enroll', ['lesson' => $lesson->slug]),
                [
                    'study_pace' => Lesson::PACE_REGULAR,
                ]
            )
            ->assertRedirect(
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

    public function test_ineligible_instructor_short_link_is_rejected(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson = $this->publishedLesson('protected-short-link-lesson');

        $link = InstructorEnrollmentLink::query()->create([
            'lesson_id' => $lesson->id,
            'instructor_id' => $instructor->id,
            'code' => 'ZX9P4M',
        ]);

        $this
            ->get(route('lessons.instructor-join', ['code' => $link->code]))
            ->assertNotFound();
    }

    public function test_unknown_short_code_is_rejected(): void
    {
        $this
            ->get(route('lessons.instructor-join', ['code' => 'NOPE99']))
            ->assertNotFound();
    }

    private function publishedLesson(string $slug): Lesson
    {
        return Lesson::query()->create([
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'description' => 'Lesson used to test instructor enrollment links.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);
    }
}
