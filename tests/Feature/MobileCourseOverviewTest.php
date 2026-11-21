<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileCourseOverviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.course_overview_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => true,
        ]]);
        DB::setDefaultConnection('course_overview_test');

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_published');
            $table->timestamps();
        });
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained();
            $table->string('title');
            $table->unsignedInteger('order');
            $table->boolean('is_published');
            $table->timestamps();
        });
        Schema::create('lesson_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained();
            $table->unsignedInteger('order');
            $table->boolean('is_published');
        });
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->unsignedBigInteger('lesson_topic_id')->nullable();
            $table->string('title');
            $table->string('quiz_type');
            $table->unsignedInteger('pass_mark')->default(70);
            $table->boolean('is_published');
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained();
            $table->string('question');
            $table->string('type');
            $table->string('correct_answer');
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    public function test_public_course_shows_website_metadata_without_exposing_quiz_answers(): void
    {
        $lesson = Lesson::create([
            'title' => 'Masomo ya Biblia',
            'slug' => 'masomo-ya-biblia',
            'description' => 'Jifunze Biblia.',
            'content' => '<p>Neno lako ndiyo kweli.</p><p>Soma kwa makini.</p>',
            'estimated_duration_minutes' => 150,
            'cover_image' => 'lesson-covers/biblia.jpg',
            'is_published' => true,
        ]);
        $this->getJson('/api/mobile/v1/lessons')
            ->assertOk()
            ->assertJsonPath('data.0.cover_image_url', url(Storage::disk('public')->url($lesson->cover_image)));
        Module::create([
            'lesson_id' => $lesson->id,
            'title' => 'Utangulizi',
            'order' => 1,
            'is_published' => true,
        ]);
        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Jaribio la Mwisho',
            'quiz_type' => 'kupimwa',
            'is_published' => true,
            'is_required' => true,
            'pass_mark' => 100,
        ]);
        foreach ([true, false] as $active) {
            $quiz->questions()->create([
                'question' => 'Swali la mfano',
                'type' => 'true_false',
                'correct_answer' => 'true',
                'is_active' => $active,
            ]);
        }

        $this->getJson("/api/mobile/v1/lessons/{$lesson->slug}")
            ->assertOk()
            ->assertJsonPath('data.description', 'Jifunze Biblia.')
            ->assertJsonPath('data.content', "Neno lako ndiyo kweli.\n\nSoma kwa makini.")
            ->assertJsonPath('data.duration_label', '2 saa 30 dakika')
            ->assertJsonPath('data.cover_image_url', url(Storage::disk('public')->url($lesson->cover_image)))
            ->assertJsonPath('data.certificate_available', true)
            ->assertJsonPath('data.final_quiz', [
                'question_count' => 1,
                'pass_mark' => 100,
                'required_for_certificate' => true,
            ]);
    }

    public function test_course_without_published_modules_omits_certificate_and_draft_quiz_metadata(): void
    {
        $lesson = Lesson::create([
            'title' => 'Somo Jipya',
            'slug' => 'somo-jipya',
            'is_published' => true,
        ]);
        $this->getJson('/api/mobile/v1/lessons')
            ->assertOk()
            ->assertJsonStructure(['data' => [['cover_image_url']]])
            ->assertJsonPath('data.0.cover_image_url', null);
        Module::create([
            'lesson_id' => $lesson->id,
            'title' => 'Rasimu',
            'order' => 1,
            'is_published' => false,
        ]);
        Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Rasimu',
            'quiz_type' => 'kupimwa',
            'is_published' => false,
        ]);

        $this->getJson("/api/mobile/v1/lessons/{$lesson->slug}")
            ->assertOk()
            ->assertJsonPath('data.final_quiz', null)
            ->assertJsonPath('data.cover_image_url', null)
            ->assertJsonPath('data.certificate_available', false);
    }
}
