<?php

namespace Tests\Feature;

use App\Filament\Widgets\DashboardStats;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsInstructorAccessTest extends TestCase
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
            'description' => 'Dashboard stats test.',
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

    private function getStatsFor(User $user): array
    {
        $this->actingAs($user);

        $widget = app(DashboardStats::class);

        $reflection = new \ReflectionMethod(
            DashboardStats::class,
            'getStats'
        );

        $reflection->setAccessible(true);

        return $reflection->invoke($widget);
    }

    public function test_follow_up_instructor_stats_count_only_assigned_students(): void
    {
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson = $this->createLesson('Shared Course');

        $lesson->followUpInstructors()->attach([
            $john->id,
            $mary->id,
        ]);

        $this->createEnrollment(
            $lesson,
            $this->createStudent('John Student'),
            $john
        );

        $this->createEnrollment(
            $lesson,
            $this->createStudent('Mary Student'),
            $mary
        );

        $stats = $this->getStatsFor($john);

        $this->assertSame(
            '1',
            (string) $stats[0]->getValue()
        );

        $this->assertSame(
            '1',
            (string) $stats[1]->getValue()
        );

        $this->assertSame(
            '1',
            (string) $stats[2]->getValue()
        );
    }

    public function test_lead_instructor_stats_count_all_course_students(): void
    {
        $lead = $this->createInstructor('Lead');
        $john = $this->createInstructor('John');
        $mary = $this->createInstructor('Mary');

        $lesson = $this->createLesson(
            'Lead Course',
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
            $this->createStudent('Student One'),
            $john
        );

        $this->createEnrollment(
            $lesson,
            $this->createStudent('Student Two'),
            $mary
        );

        $stats = $this->getStatsFor($lead);

        $this->assertSame(
            '1',
            (string) $stats[0]->getValue()
        );

        $this->assertSame(
            '2',
            (string) $stats[1]->getValue()
        );

        $this->assertSame(
            '2',
            (string) $stats[2]->getValue()
        );
    }

    public function test_legacy_instructor_stats_still_work(): void
    {
        $instructor = $this->createInstructor(
            'Legacy Instructor'
        );

        $lesson = $this->createLesson(
            'Legacy Course',
            [
                'instructor_id' => $instructor->id,
            ]
        );

        $this->createEnrollment(
            $lesson,
            $this->createStudent('Legacy Student')
        );

        $stats = $this->getStatsFor($instructor);

        $this->assertSame(
            '1',
            (string) $stats[0]->getValue()
        );

        $this->assertSame(
            '1',
            (string) $stats[1]->getValue()
        );
    }
}