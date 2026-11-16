<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonTopic;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobileStudyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.mobile_study_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => true,
        ]]);
        DB::setDefaultConnection('mobile_study_test');
        Notification::fake();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
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
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->unsignedInteger('order')->default(1);
            $table->boolean('is_published');
            $table->timestamps();
        });
        Schema::create('lesson_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('lesson_id')->constrained();
            $table->timestamp('enrolled_at')->nullable();
            $table->string('study_pace')->nullable();
            $table->unsignedInteger('study_hours_per_week')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->timestamp('schedule_started_at')->nullable();
            $table->timestamp('schedule_updated_at')->nullable();
            $table->unique(['user_id', 'lesson_id']);
            $table->timestamps();
        });
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('lesson_id')->constrained();
            $table->foreignId('lesson_topic_id')->constrained();
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'lesson_topic_id']);
            $table->timestamps();
        });
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable();
            $table->foreignId('lesson_topic_id')->nullable();
            $table->boolean('is_published');
            $table->boolean('is_required');
            $table->timestamps();
        });
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->boolean('passed');
            $table->timestamps();
        });
        (require database_path('migrations/2026_11_16_200000_create_personal_access_tokens_table.php'))->up();

        // The assignment service has its own tests and production tables.
        $this->mock(\App\Services\FollowUpInstructorAssignmentService::class)
            ->shouldReceive('assign')->andReturnNull();
    }

    public function test_student_signs_in_enrolls_reads_and_resumes_in_the_api(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $first = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $second = $lesson->modules()->create(['title' => 'Pili', 'order' => 2, 'is_published' => true]);
        $topic = $this->topic($first, 'imani');
        $this->topic($second, 'tumaini');
        $base = '/api/mobile/v1/study/lessons/biblia';

        $this->getJson($base)->assertUnauthorized();
        $token = $this->postJson('/api/mobile/v1/study/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('data.token');

        $this->withToken($token)->getJson($base)->assertOk()->assertJsonPath('data.enrolled', false);
        $this->getJson($base.'/topics/imani')->assertForbidden();
        $this->postJson($base.'/enroll')->assertOk()->assertJsonPath('data.enrolled', true);
        $this->postJson($base.'/enroll')->assertOk();
        $this->assertDatabaseCount('lesson_enrollments', 1);
        $this->getJson($base.'/topics/tumaini')->assertForbidden();
        $quiz = $topic->quiz()->create(['is_published' => true, 'is_required' => true]);
        $this->getJson($base.'/topics/imani')->assertOk()
            ->assertJsonPath('data.content', 'Soma Biblia.')
            ->assertJsonPath('data.can_complete', false);
        $this->postJson($base.'/topics/imani/complete')->assertUnprocessable();
        $quiz->results()->create(['user_id' => $user->id, 'passed' => true]);
        $this->postJson($base.'/topics/imani/complete')->assertOk()->assertJsonPath('data.completed', true);
        $this->postJson($base.'/topics/imani/complete')->assertOk();
        $this->assertDatabaseCount('lesson_progress', 1);
        $this->assertDatabaseHas('lesson_progress', ['user_id' => $user->id, 'lesson_topic_id' => $topic->id]);
        $this->getJson($base)->assertOk()->assertJsonPath('data.resume_topic', 'tumaini');
        $this->deleteJson('/api/mobile/v1/study/session')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->getJson($base)->assertUnauthorized();
    }

    private function topic($module, string $slug): LessonTopic
    {
        return $module->topics()->create([
            'title' => $slug,
            'slug' => $slug,
            'content' => '<p>Soma Biblia.</p>',
            'is_published' => true,
        ]);
    }
}
