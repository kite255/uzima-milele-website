<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonIndexStartLearningCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_start_learning_button_that_preserves_lesson_destination(): void
    {
        $lesson = $this->publishedLesson('guest-start-learning');

        $returnUrl = route('lessons.show', ['lesson' => $lesson->slug])
            . '#learning-schedule';

        $this->get(route('lessons.index'))
            ->assertOk()
            ->assertSee('Ingia Kuanza Kujifunza')
            ->assertSee(
                route('login', ['redirect' => $returnUrl]),
                false
            );
    }

    public function test_logged_in_unenrolled_student_sees_join_lesson_button(): void
    {
        $lesson = $this->publishedLesson('join-from-list');

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->get(route('lessons.index'))
            ->assertOk()
            ->assertSee('Jiunge na Somo')
            ->assertSee(
                route('lessons.show', ['lesson' => $lesson->slug])
                . '#learning-schedule',
                false
            );
    }

    public function test_enrolled_student_sees_continue_learning_button(): void
    {
        $lesson = $this->publishedLesson('continue-from-list');

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('lessons.index'))
            ->assertOk()
            ->assertSee('Endelea Kujifunza')
            ->assertSee(
                route('lessons.learn', ['lesson' => $lesson->slug]),
                false
            );
    }

    private function publishedLesson(string $slug): Lesson
    {
        return Lesson::query()->create([
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'description' => 'Lesson used to test the lesson list CTA.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);
    }
}
