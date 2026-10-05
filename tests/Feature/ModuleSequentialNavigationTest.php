<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonTopic;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModuleSequentialNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_open_later_module_before_previous_module_is_complete(): void
    {
        $user = User::factory()->create();

        $lesson = Lesson::create([
            'title' => 'Sequential Module Lesson',
            'slug' => 'sequential-module-' . Str::random(8),
            'is_published' => true,
        ]);

        $moduleOne = Module::create([
            'lesson_id' => $lesson->id,
            'title' => 'Module One',
            'order' => 1,
            'is_published' => true,
        ]);

        $topicOne = LessonTopic::forceCreate([
            'module_id' => $moduleOne->id,
            'title' => 'Topic One',
            'slug' => 'topic-one-' . Str::random(8),
            'order' => 1,
            'is_published' => true,
        ]);

        $moduleTwo = Module::create([
            'lesson_id' => $lesson->id,
            'title' => 'Module Two',
            'order' => 2,
            'is_published' => true,
        ]);

        $topicTwo = LessonTopic::forceCreate([
            'module_id' => $moduleTwo->id,
            'title' => 'Topic Two',
            'slug' => 'topic-two-' . Str::random(8),
            'order' => 1,
            'is_published' => true,
        ]);

        LessonEnrollment::forceCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'enrolled_at' => now(),
            'study_pace' => Lesson::PACE_REGULAR,
            'study_hours_per_week' => 3,
            'schedule_started_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('lessons.learn', [
                'lesson' => $lesson->slug,
                'topic' => $topicTwo->id,
            ]));

        $response->assertRedirect(route('lessons.learn', [
            'lesson' => $lesson->slug,
            'topic' => $topicOne->id,
        ]));

        $response->assertSessionHas('error');
    }
}
