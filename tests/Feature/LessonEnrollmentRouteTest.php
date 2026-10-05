<?php

namespace Tests\Feature;

use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonEnrollmentRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_enroll_url_redirects_back_to_lesson_page(): void
    {
        $lesson = Lesson::query()->create([
            'title' => 'Enrollment Route Lesson',
            'slug' => 'enrollment-route-lesson',
            'description' => 'Lesson used to test the enrollment route fallback.',
            'is_published' => true,
            'recommended_study_pace' => Lesson::PACE_REGULAR,
            'lead_can_receive_students' => false,
        ]);

        $this
            ->get('/lessons/' . $lesson->slug . '/enroll')
            ->assertRedirect(
                route('lessons.show', ['lesson' => $lesson->slug])
            );
    }
}
