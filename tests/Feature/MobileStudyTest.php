<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\LessonProgress;
use App\Models\LessonTopic;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use App\Notifications\LessonQuestionAskedNotification;
use App\Notifications\LessonQuestionAnsweredNotification;
use App\Notifications\LessonQuestionEscalatedNotification;
use App\Notifications\LessonQuestionReminderNotification;
use App\Services\CertificatePdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Firebase\JWT\JWT;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
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
            $table->string('google_id')->nullable()->unique();
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
            $table->string('cover_image')->nullable();
            $table->foreignId('prerequisite_lesson_id')->nullable()->constrained('lessons');
            $table->foreignId('lead_instructor_id')->nullable()->constrained('users');
            $table->foreignId('instructor_id')->nullable()->constrained('users');
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
            $table->string('video_url')->nullable();
            $table->string('pdf')->nullable();
            $table->unsignedInteger('order')->default(1);
            $table->boolean('is_published');
            $table->timestamps();
        });
        Schema::create('lesson_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('lesson_id')->constrained();
            $table->foreignId('follow_up_instructor_id')->nullable()->constrained('users');
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
            $table->foreignId('lesson_id')->nullable();
            $table->foreignId('lesson_topic_id')->nullable();
            $table->string('title')->default('Mtihani');
            $table->string('quiz_type')->default('kupimwa');
            $table->unsignedInteger('pass_mark')->default(70);
            $table->boolean('is_published');
            $table->boolean('is_required');
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained();
            $table->text('question');
            $table->string('type');
            $table->string('correct_answer')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unsignedInteger('score');
            $table->unsignedInteger('correct_answers');
            $table->unsignedInteger('total_questions');
            $table->boolean('passed');
            $table->timestamps();
        });
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('user_name')->nullable();
            $table->foreignId('lesson_topic_id')->nullable();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('correct')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->boolean('passed');
            $table->timestamps();
        });
        (require database_path('migrations/2026_04_30_130701_create_certificates_table.php'))->up();
        (require database_path('migrations/2026_11_16_200000_create_personal_access_tokens_table.php'))->up();
        (require database_path('migrations/2026_05_02_213507_create_lesson_questions_table.php'))->up();
        (require database_path('migrations/2026_05_17_082635_add_lesson_topic_id_and_visibility_to_lesson_questions_table.php'))->up();
        (require database_path('migrations/2026_09_10_190114_create_lesson_follow_up_instructor_table.php'))->up();
        (require database_path('migrations/2026_10_01_210000_add_question_escalation_timestamps_to_lesson_questions_table.php'))->up();

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

        $resume = '/api/mobile/v1/study/resume';
        $this->getJson($resume)->assertUnauthorized();
        $this->getJson($base)->assertUnauthorized();
        $token = $this->postJson('/api/mobile/v1/study/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('data.token');

        $this->withToken($token)->getJson($resume)->assertOk()->assertJsonPath('data.lesson', null);
        $this->getJson($base)->assertOk()->assertJsonPath('data.enrolled', false);
        $this->getJson($base.'/topics/imani')->assertForbidden();
        $this->postJson($base.'/enroll')->assertOk()->assertJsonPath('data.enrolled', true);
        $this->postJson($base.'/enroll')->assertOk();
        $this->assertDatabaseCount('lesson_enrollments', 1);
        $this->getJson($resume)->assertOk()->assertJsonPath('data.topic.slug', 'imani');
        $this->getJson($base)->assertOk()->assertJsonPath('data.locked_topics', ['tumaini']);
        $this->getJson($base.'/topics/tumaini')->assertForbidden();
        $quiz = $topic->quiz()->create(['is_published' => true, 'is_required' => true]);
        $this->getJson($base.'/topics/imani')->assertOk()
            ->assertJsonPath('data.content', 'Soma Biblia.')
            ->assertJsonPath('data.can_complete', false);
        $this->postJson($base.'/topics/imani/complete')->assertUnprocessable();
        $question = $quiz->questions()->create([
            'question' => 'Kweli au si kweli?', 'type' => 'true_false', 'correct_answer' => 'true',
        ]);
        $moduleQuiz = $first->quizzes()->create(['is_published' => true, 'is_required' => true]);
        $moduleQuestion = $moduleQuiz->questions()->create([
            'question' => 'Chagua jibu.', 'type' => 'multiple_choice',
            'options' => [['text' => 'Ndiyo', 'is_correct' => true], ['text' => 'Hapana', 'is_correct' => false]],
        ]);
        $finalQuiz = $lesson->finalQuiz()->create(['is_published' => true, 'is_required' => true]);
        $finalQuestion = $finalQuiz->questions()->create([
            'question' => 'Kweli au si kweli?', 'type' => 'true_false', 'correct_answer' => 'false',
        ]);
        $quizPath = $base.'/quizzes/'.$quiz->id;
        $moduleQuizPath = $base.'/quizzes/'.$moduleQuiz->id;
        $finalQuizPath = $base.'/quizzes/'.$finalQuiz->id;
        $this->getJson($moduleQuizPath)->assertForbidden();
        $this->postJson($finalQuizPath, ['answers' => [$finalQuestion->id => 'false']])->assertForbidden();
        $this->getJson($quizPath)->assertOk()
            ->assertJsonPath('data.questions.0.id', $question->id)
            ->assertJsonMissingPath('data.questions.0.correct_answer');
        $otherLesson = Lesson::create(['title' => 'Nyingine', 'slug' => 'nyingine', 'is_published' => true]);
        $otherModule = $otherLesson->modules()->create(['title' => 'Nyingine', 'order' => 1, 'is_published' => true]);
        $otherTopic = $this->topic($otherModule, 'rafiki');
        $otherQuiz = $otherTopic->quiz()->create(['is_published' => true, 'is_required' => true]);
        $this->getJson($base.'/quizzes/'.$otherQuiz->id)->assertNotFound();
        $this->postJson($quizPath, ['answers' => [$question->id => 'other']])->assertUnprocessable();
        $this->assertDatabaseCount('quiz_attempts', 0);
        $this->postJson($quizPath, ['answers' => [$question->id => 'false']])->assertOk()
            ->assertJsonPath('data.passed', false);
        $this->assertDatabaseCount('lesson_progress', 0);
        $this->postJson($quizPath, ['answers' => [$question->id => 'true']])->assertOk()
            ->assertJsonPath('data.passed', true);
        $this->assertDatabaseCount('lesson_progress', 1);
        $this->postJson($base.'/topics/imani/complete')->assertOk()->assertJsonPath('data.completed', true);
        $this->postJson($base.'/topics/imani/complete')->assertOk();
        $this->assertDatabaseCount('lesson_progress', 1);
        $this->assertDatabaseHas('lesson_progress', ['user_id' => $user->id, 'lesson_topic_id' => $topic->id]);
        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.resume_topic', null)
            ->assertJsonPath('data.resume_quiz.id', $moduleQuiz->id);
        $this->getJson($resume.'?include_quizzes=1')->assertOk()->assertJsonPath('data.quiz.id', $moduleQuiz->id);
        $this->getJson($resume)->assertOk()->assertJsonPath('data.lesson', null);
        $this->getJson($base.'/topics/tumaini')->assertForbidden();
        $this->getJson($moduleQuizPath)->assertOk()->assertJsonPath('data.questions.0.options.0.label', 'Ndiyo')
            ->assertJsonMissingPath('data.questions.0.options.0.is_correct');
        $this->postJson($moduleQuizPath, ['answers' => [$moduleQuestion->id => '1']])->assertOk()
            ->assertJsonPath('data.passed', false);
        $this->getJson($base.'/topics/tumaini')->assertForbidden();
        $this->getJson($base)->assertOk()->assertJsonPath('data.certificate_eligible', false);
        $this->postJson($base.'/certificate')->assertUnprocessable();
        $this->postJson($moduleQuizPath, ['answers' => [$moduleQuestion->id => '0']])->assertOk()
            ->assertJsonPath('data.passed', true);
        $this->getJson($base)->assertOk()->assertJsonPath('data.resume_topic', 'tumaini');
        $this->getJson($resume)->assertOk()
            ->assertJsonPath('data.lesson', ['slug' => 'biblia', 'title' => 'Biblia'])
            ->assertJsonPath('data.topic', ['slug' => 'tumaini', 'title' => 'tumaini']);
        $this->postJson($base.'/topics/tumaini/complete')->assertOk();
        $this->getJson($resume.'?include_quizzes=1')->assertOk()->assertJsonPath('data.quiz.id', $finalQuiz->id);
        $this->getJson($base)->assertOk()->assertJsonPath('data.lesson_completed', false);
        $this->postJson($finalQuizPath, ['answers' => [$finalQuestion->id => 'true']])->assertOk()
            ->assertJsonPath('data.passed', false);
        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.lesson_completed', false)
            ->assertJsonPath('data.final_quiz_score', null)
            ->assertJsonPath('data.certificate_issued', false)
            ->assertJsonPath('data.certificate_eligible', false);
        $this->postJson($base.'/certificate')->assertUnprocessable();
        $this->postJson($finalQuizPath, ['answers' => [$finalQuestion->id => 'false']])->assertOk();
        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.lesson_completed', true)
            ->assertJsonPath('data.final_quiz_score', 100)
            ->assertJsonPath('data.certificate_issued', false)
            ->assertJsonPath('data.certificate_eligible', true);
        $this->assertDatabaseCount('certificates', 0);
        $this->getJson($resume)->assertOk()->assertJsonPath('data.lesson', null);
        $this->deleteJson('/api/mobile/v1/study/session')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->getJson($base)->assertUnauthorized();
    }

    public function test_my_courses_requires_a_student_study_token_and_returns_an_empty_list(): void
    {
        $endpoint = '/api/mobile/v1/study/my-courses';
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->getJson($endpoint)->assertUnauthorized();
        $this->withToken($student->createToken('Account only', ['account'])->plainTextToken)
            ->getJson($endpoint)->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($admin->createToken('Study test', ['study'])->plainTextToken)
            ->getJson($endpoint)->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken)
            ->getJson($endpoint)->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertExactJson(['data' => ['courses' => []]]);
    }

    public function test_my_courses_returns_only_the_students_published_enrollments_and_their_progress(): void
    {
        config(['filesystems.disks.public.url' => 'https://study.example.test/storage']);
        $endpoint = '/api/mobile/v1/study/my-courses';
        $student = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);
        $course = Lesson::create([
            'title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true,
            'cover_image' => 'lessons/covers/biblia.jpg',
        ]);
        $otherCourse = Lesson::create(['title' => 'Nyingine', 'slug' => 'nyingine', 'is_published' => true]);
        $draft = Lesson::create(['title' => 'Rasimu', 'slug' => 'rasimu', 'is_published' => false]);
        $locked = Lesson::create([
            'title' => 'Pili', 'slug' => 'pili', 'is_published' => true,
            'prerequisite_lesson_id' => $course->id,
        ]);
        $module = $course->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $this->topic($module, 'imani');
        $second = $this->topic($module, 'tumaini');
        $second->update(['order' => 2]);
        $this->topic($module, 'rasimu-ya-mada')->update(['is_published' => false]);
        $draftModule = $course->modules()->create(['title' => 'Rasimu', 'order' => 2, 'is_published' => false]);
        $this->topic($draftModule, 'mada-iliyofichwa');
        $lockedModule = $locked->modules()->create(['title' => 'Pili', 'order' => 1, 'is_published' => true]);
        $this->topic($lockedModule, 'subira');
        $final = $course->finalQuiz()->create(['title' => 'Mtihani wa mwisho', 'is_published' => true, 'is_required' => true]);
        $student->lessonEnrollments()->forceCreate(['lesson_id' => $draft->id, 'enrolled_at' => now()]);
        $student->lessonEnrollments()->forceCreate(['lesson_id' => $locked->id, 'enrolled_at' => now()]);
        $other->lessonEnrollments()->forceCreate(['lesson_id' => $otherCourse->id, 'enrolled_at' => now()]);
        LessonProgress::create([
            'user_id' => $other->id, 'lesson_id' => $course->id,
            'lesson_topic_id' => $second->id, 'completed_at' => now(),
        ]);
        Certificate::create([
            'lesson_id' => $course->id, 'user_id' => $other->id,
            'certificate_number' => 'UZM-OTHER', 'issued_at' => now(),
        ]);
        $final->results()->create(['user_id' => $other->id, 'score' => 100, 'passed' => true]);

        $courseUrl = '/api/mobile/v1/study/lessons/biblia';
        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken);
        $this->postJson($courseUrl.'/enroll')->assertOk();
        $this->postJson($courseUrl.'/topics/imani/complete')->assertOk();

        $this->getJson($endpoint)->assertOk()->assertExactJson(['data' => ['courses' => [[
            'slug' => 'biblia', 'title' => 'Biblia',
            'cover_image_url' => 'https://study.example.test/storage/lessons/covers/biblia.jpg',
            'can_start' => true, 'lesson_completed' => false, 'completed_topic_count' => 1,
            'total_topic_count' => 2, 'next_title' => 'tumaini', 'final_quiz_score' => null,
            'certificate_issued' => false, 'certificate_eligible' => false,
        ], [
            'slug' => 'pili', 'title' => 'Pili', 'cover_image_url' => null,
            'can_start' => false, 'lesson_completed' => false, 'completed_topic_count' => 0,
            'total_topic_count' => 1, 'next_title' => null, 'final_quiz_score' => null,
            'certificate_issued' => false, 'certificate_eligible' => false,
        ]]]]);

        $this->postJson($courseUrl.'/topics/tumaini/complete')->assertOk();
        $this->getJson($endpoint)->assertOk()
            ->assertJsonPath('data.courses.0.next_title', 'Mtihani wa mwisho')
            ->assertJsonPath('data.courses.0.lesson_completed', false)
            ->assertJsonPath('data.courses.0.final_quiz_score', null);

        $final->results()->create(['user_id' => $student->id, 'score' => 90, 'passed' => true]);
        $this->getJson($endpoint)->assertOk()
            ->assertJsonPath('data.courses.0.completed_topic_count', 2)
            ->assertJsonPath('data.courses.0.lesson_completed', true)
            ->assertJsonPath('data.courses.0.next_title', null)
            ->assertJsonPath('data.courses.0.final_quiz_score', 90)
            ->assertJsonPath('data.courses.0.certificate_eligible', true)
            ->assertJsonPath('data.courses.1.can_start', true)
            ->assertJsonPath('data.courses.1.next_title', 'subira');

        $this->postJson($courseUrl.'/certificate')->assertOk();
        $course->update(['prerequisite_lesson_id' => $otherCourse->id]);
        $this->getJson($endpoint)->assertOk()
            ->assertJsonPath('data.courses.0.can_start', false)
            ->assertJsonPath('data.courses.0.lesson_completed', true)
            ->assertJsonPath('data.courses.0.final_quiz_score', 90)
            ->assertJsonPath('data.courses.0.certificate_issued', true)
            ->assertJsonPath('data.courses.0.certificate_eligible', false);
    }

    public function test_completion_shows_the_students_best_passing_published_final_quiz_score(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $this->topic($module, 'imani');
        $final = $lesson->finalQuiz()->create(['is_published' => true, 'is_required' => true]);
        $draft = $lesson->finalQuiz()->create(['is_published' => false, 'is_required' => true]);
        $base = '/api/mobile/v1/study/lessons/biblia';
        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken);
        $this->postJson($base.'/enroll')->assertOk();
        $this->postJson($base.'/topics/imani/complete')->assertOk();

        $final->results()->create(['user_id' => $student->id, 'score' => 80, 'passed' => true]);
        $final->results()->create(['user_id' => $student->id, 'score' => 95, 'passed' => false]);
        $final->results()->create(['user_id' => $other->id, 'score' => 100, 'passed' => true]);
        $final->attempts()->create([
            'user_id' => $student->id, 'score' => 90, 'passed' => true,
            'correct_answers' => 9, 'total_questions' => 10,
        ]);
        $draft->results()->create(['user_id' => $student->id, 'score' => 100, 'passed' => true]);

        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.lesson_completed', true)
            ->assertJsonPath('data.final_quiz_score', 90)
            ->assertJsonPath('data.certificate_issued', false);
    }

    public function test_completed_course_with_optional_final_quiz_reports_a_recorded_zero(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $this->topic($module, 'imani');
        $final = $lesson->finalQuiz()->create(['is_published' => true, 'is_required' => false]);
        $base = '/api/mobile/v1/study/lessons/biblia';
        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken);
        $this->postJson($base.'/enroll')->assertOk();
        $this->postJson($base.'/topics/imani/complete')->assertOk();

        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.lesson_completed', true)
            ->assertJsonPath('data.final_quiz_score', null)
            ->assertJsonPath('data.certificate_eligible', true);
        $final->results()->create(['user_id' => $student->id, 'score' => 0, 'passed' => false]);

        $this->getJson($base)->assertOk()->assertJsonPath('data.final_quiz_score', 0);
    }

    public function test_a_student_can_claim_only_their_own_certificate_once_after_completing_the_lesson(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $this->topic($module, 'imani');
        $base = '/api/mobile/v1/study/lessons/biblia';
        $claim = $base.'/certificate';

        $this->postJson($claim)->assertUnauthorized();
        $this->withToken($student->createToken('Account only', ['account'])->plainTextToken)
            ->postJson($claim)->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken);
        $this->getJson($base)->assertOk()->assertJsonPath('data.certificate_eligible', false);
        $this->postJson($claim)->assertForbidden();
        $this->postJson($base.'/enroll')->assertOk();
        $this->postJson($claim)->assertUnprocessable();
        $this->assertDatabaseCount('certificates', 0);

        $this->postJson($base.'/topics/imani/complete')->assertOk();
        Certificate::create([
            'lesson_id' => $lesson->id, 'user_id' => $other->id,
            'certificate_number' => 'UZM-OTHER', 'issued_at' => now(),
        ]);
        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.certificate_issued', false)
            ->assertJsonPath('data.certificate_eligible', true);

        $issued = ['data' => ['certificate_issued' => true, 'certificate_eligible' => false]];
        $this->postJson($claim)->assertOk()->assertExactJson($issued);
        $certificate = Certificate::where('user_id', $student->id)->sole();
        $this->assertSame($lesson->id, $certificate->lesson_id);
        $this->assertMatchesRegularExpression('/^UZM-\d{4}-[A-Z0-9]{8}$/', $certificate->certificate_number);
        $this->postJson($claim)->assertOk()->assertExactJson($issued);
        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.certificate_issued', true)
            ->assertJsonPath('data.certificate_eligible', false);
        $this->assertDatabaseCount('certificates', 2);
        Notification::assertSentToTimes($student, CertificateIssuedNotification::class, 1);
        Notification::assertNotSentTo($other, CertificateIssuedNotification::class);

        $lesson->update(['is_published' => false]);
        $this->getJson($base)->assertNotFound();
        $this->postJson($claim)->assertNotFound();
    }

    public function test_a_failed_certificate_notification_does_not_fail_a_claim_or_run_again_on_retry(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $this->topic($module, 'imani');
        $base = '/api/mobile/v1/study/lessons/biblia';
        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken);
        $this->postJson($base.'/enroll')->assertOk();
        $this->postJson($base.'/topics/imani/complete')->assertOk();

        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Notification delivery failed'));
        $this->postJson($base.'/certificate')->assertOk()->assertJsonPath('data.certificate_issued', true);
        $this->postJson($base.'/certificate')->assertOk()->assertJsonPath('data.certificate_issued', true);
        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_only_the_owner_can_download_an_already_issued_certificate(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $this->topic($module, 'imani');
        $base = '/api/mobile/v1/study/lessons/biblia';
        $download = $base.'/certificate';

        $this->getJson($download)->assertUnauthorized();
        $this->withToken($student->createToken('Study test', ['account'])->plainTextToken)
            ->getJson($download)->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken);
        $this->postJson($base.'/enroll')->assertOk();
        $this->postJson($base.'/topics/imani/complete')->assertOk();

        Certificate::create([
            'lesson_id' => $lesson->id, 'user_id' => $other->id,
            'certificate_number' => 'UZM-OTHER', 'issued_at' => now(),
        ]);
        $this->getJson($download)->assertNotFound();
        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.lesson_completed', true)
            ->assertJsonPath('data.certificate_issued', false);
        $this->assertDatabaseCount('certificates', 1);

        $this->actingAs($student, 'web')->post(route('certificates.issue', $lesson->id))->assertRedirect();
        $certificate = Certificate::where('user_id', $student->id)->sole();
        $this->actingAs($student, 'web')->post(route('certificates.issue', $lesson->id))
            ->assertRedirect(route('certificates.show', $certificate->certificate_number));
        $this->app['auth']->forgetGuards();
        $this->getJson($base)->assertOk()
            ->assertJsonPath('data.certificate_issued', true)
            ->assertJsonPath('data.certificate_eligible', false);
        $this->postJson($download)->assertOk()->assertJsonPath('data.certificate_issued', true);
        Notification::assertSentToTimes($student, CertificateIssuedNotification::class, 1);

        $pdf = $this->getJson($download)->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonStructure(['data' => ['pdf_base64']])
            ->assertJsonMissingPath('data.url')
            ->json('data.pdf_base64');

        $this->assertStringStartsWith('%PDF-', base64_decode($pdf, true));
        $this->assertDatabaseCount('certificates', 2);
    }

    public function test_certificate_download_rejects_an_oversized_pdf(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        Certificate::create([
            'lesson_id' => $lesson->id, 'user_id' => $student->id,
            'certificate_number' => 'UZM-OWNER', 'issued_at' => now(),
        ]);
        $domPdf = $this->mock(DomPdf::class);
        $domPdf->shouldReceive('output')->once()->andReturn(str_repeat('x', 5 * 1024 * 1024 + 1));
        $this->mock(CertificatePdf::class)->shouldReceive('render')->once()->andReturn($domPdf);

        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken)
            ->getJson('/api/mobile/v1/study/lessons/biblia/certificate')
            ->assertStatus(500)
            ->assertJsonMissingPath('data.pdf_base64');
    }

    public function test_the_website_quiz_keeps_its_result_and_review(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $topic = $this->topic($module, 'imani');
        $quiz = $topic->quiz()->create(['is_published' => true, 'is_required' => true]);
        $question = $quiz->questions()->create([
            'question' => 'Kweli au si kweli?', 'type' => 'true_false', 'correct_answer' => 'true',
        ]);

        $this->actingAs($student)
            ->post(action([\App\Http\Controllers\QuizController::class, 'submit'], ['quiz' => $quiz->id]), [
                'question_'.$question->id => 'true',
            ])
            ->assertRedirect()
            ->assertSessionHas('result.passed', true)
            ->assertSessionHas('result.score', 100)
            ->assertSessionHas('review.'.$question->id.'.correct_answer', 'true');
        $this->assertDatabaseHas('lesson_progress', ['user_id' => $student->id, 'lesson_topic_id' => $topic->id]);
    }

    public function test_topic_returns_existing_video_pdf_and_inline_media_in_content_order(): void
    {
        config(['filesystems.disks.public.url' => 'https://study.example.test/storage']);
        $user = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create([
            'title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true,
            'cover_image' => 'lessons/covers/biblia.jpg',
        ]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $topic = $this->topic($module, 'imani');
        $topic->update([
            'content' => '<h2>Imani</h2><p>Mwanzo</p><img src="/storage/topics/mandhari.jpg" alt="Mandhari">'
                .'<p>Mwisho</p><audio><source src="/storage/topics/sauti.mp3" type="audio/mpeg"></audio>'
                .'<video src="https://cdn.example.test/video.mp4"></video>'
                .'<iframe src="https://www.youtube.com/embed/abcdefghijk"></iframe>',
            'video_url' => 'https://youtu.be/abcdefghijk',
            'pdf' => 'lessons/topics/pdfs/Imani ya leo.pdf',
        ]);
        $lesson->enrollments()->create(['user_id' => $user->id]);

        $response = $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('https://study.example.test/api/mobile/v1/study/lessons/biblia/topics/imani');

        $response->assertOk()
            ->assertJsonPath('data.title', 'imani')
            ->assertJsonPath('data.video_url', 'https://youtu.be/abcdefghijk')
            ->assertJsonPath('data.pdf_url', 'https://study.example.test/storage/lessons/topics/pdfs/Imani%20ya%20leo.pdf')
            ->assertJsonPath('data.cover_image_url', 'https://study.example.test/storage/lessons/covers/biblia.jpg')
            ->assertJsonPath('data.completed', false)
            ->assertJsonPath('data.can_complete', true);
        $this->assertStringContainsString('Mwanzo', $response->json('data.content'));
        $html = $response->json('data.content_html');
        $this->assertStringContainsString(
            '<h2>Imani</h2><p>Mwanzo</p><img src="https://study.example.test/storage/topics/mandhari.jpg" alt="Mandhari"><p>Mwisho</p>',
            $html,
        );
        $this->assertMatchesRegularExpression('/<audio\b[^>]*src="https:\/\/study\.example\.test\/storage\/topics\/sauti\.mp3"/', $html);
        $this->assertStringContainsString('src="https://cdn.example.test/video.mp4"', $html);
        $this->assertStringContainsString('src="https://www.youtube-nocookie.com/embed/abcdefghijk"', $html);
    }

    public function test_topic_drops_unsafe_media_and_active_html(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $topic = $this->topic($module, 'imani');
        $topic->update([
            'content' => '<p onclick="doSomething()">Soma <strong>Biblia</strong>.</p>'
                .'<script>doSomething()</script><img src="data:image/svg+xml,unsafe" onerror="doSomething()">'
                .'<a href="javascript:doSomething()">Rejea</a>'
                .'<video src="http://media.example.test/unsafe.mp4"></video>'
                .'<iframe src="https://www.youtube.com.evil.test/embed/abcdefghijk" srcdoc="unsafe"></iframe>',
            'video_url' => 'https://user:pass@media.example.test/video.mp4',
            'pdf' => 'javascript:doSomething()',
        ]);
        $lesson->enrollments()->create(['user_id' => $user->id]);

        $response = $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/mobile/v1/study/lessons/biblia/topics/imani');

        $response->assertOk()->assertJsonPath('data.video_url', null)->assertJsonPath('data.pdf_url', null);
        $html = $response->json('data.content_html');
        $this->assertIsString($html);
        $this->assertStringContainsString('<p>Soma <strong>Biblia</strong>.</p>', $html);
        $this->assertStringContainsString('Rejea', $html);
        $this->assertDoesNotMatchRegularExpression('/<script|onclick|onerror|javascript:|<img|<video|<iframe|srcdoc|unsafe|doSomething/', $html);
    }

    public function test_google_login_verifies_the_token_and_links_an_existing_verified_gmail_student(): void
    {
        $clientId = 'study-web-client.apps.googleusercontent.com';
        $websiteClientId = 'website-web-client.apps.googleusercontent.com';
        config([
            'services.google.client_id' => $websiteClientId,
            'services.google.mobile_web_client_id' => $clientId,
        ]);
        $subject = '12345678901234567890';
        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'learner@gmail.com',
            'email_verified_at' => now(),
        ]);
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $rsa = openssl_pkey_get_details($key)['rsa'];
        $provider = new class ($this->app['request'], $clientId, $rsa) extends GoogleProvider
        {
            public function __construct($request, string $clientId, private array $rsa)
            {
                parent::__construct($request, $clientId, 'unused-secret', '/auth/google/callback');
            }

            protected function getGoogleJwks()
            {
                return ['keys' => [[
                    'kty' => 'RSA', 'alg' => 'RS256', 'kid' => 'test-key',
                    'n' => JWT::urlsafeB64Encode($this->rsa['n']),
                    'e' => JWT::urlsafeB64Encode($this->rsa['e']),
                ]]];
            }
        };
        Socialite::shouldReceive('buildProvider')
            ->with(GoogleProvider::class, [
                'client_id' => $clientId,
                'client_secret' => '',
                'redirect' => '',
            ])->andReturn($provider);
        $claims = [
            'iss' => 'https://accounts.google.com',
            'aud' => $clientId,
            'sub' => $subject,
            'email' => $student->email,
            'email_verified' => true,
            'exp' => now()->addMinute()->timestamp,
        ];
        $endpoint = '/api/mobile/v1/study/login/google';
        $signed = fn (array $overrides = []) => JWT::encode(
            array_replace($claims, $overrides), $key, 'RS256', 'test-key',
        );

        $this->postJson($endpoint, ['id_token' => 'opaque-token'])->assertUnprocessable();
        $this->postJson($endpoint, ['id_token' => $signed(['aud' => $websiteClientId])])->assertUnprocessable();
        $this->postJson($endpoint, ['id_token' => $signed(['email_verified' => false])])->assertUnprocessable();
        $this->postJson($endpoint, ['id_token' => $signed(['exp' => now()->subMinute()->timestamp])])->assertUnprocessable();
        $this->postJson($endpoint, ['id_token' => $signed(['sub' => 'different-subject', 'email' => 'new@gmail.com'])])
            ->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $token = $this->postJson($endpoint, ['id_token' => $signed()])->assertOk()->json('data.token');
        $this->assertSame($subject, $student->fresh()->google_id);
        $this->assertSame($websiteClientId, config('services.google.client_id'));
        $this->withToken($token)->getJson('/api/mobile/v1/study/resume')->assertOk();
    }

    public function test_a_student_can_ask_about_an_accessible_topic_and_read_only_their_own_questions(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $unassigned = User::factory()->create(['role' => 'instructor']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $topic = $this->topic($module, 'imani');
        $draft = $this->topic($module, 'rasimu');
        $draft->update(['is_published' => false]);
        $history = '/api/mobile/v1/study/my-questions';
        $base = '/api/mobile/v1/study/lessons/biblia/topics';
        $payload = ['question' => 'Naomba ufafanuzi kuhusu imani.', 'visibility' => 'public'];

        $this->getJson($history)->assertUnauthorized();
        $token = $student->createToken('Study test', ['study'])->plainTextToken;
        $this->withToken($token);
        $this->postJson($base.'/imani/questions', $payload)->assertForbidden();
        LessonEnrollment::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $instructor->id,
        ]);
        $this->postJson($base.'/rasimu/questions', $payload)->assertNotFound();
        $this->postJson($base.'/imani/questions', ['question' => 'Hi'])->assertUnprocessable();
        $this->postJson($base.'/imani/questions', $payload)->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.topic_title', $topic->title);

        $question = LessonQuestion::query()->sole();
        $this->assertSame($topic->id, $question->lesson_topic_id);
        $this->assertSame(LessonQuestion::VISIBILITY_PRIVATE, $question->visibility);
        Notification::assertSentTo($instructor, LessonQuestionAskedNotification::class);

        $answer = [
            'answer' => 'Imani ni kumtegemea Mungu.',
            'visibility' => 'private',
            'is_published' => true,
        ];
        $reply = route('instructor.questions.update', $question);
        $this->actingAs($unassigned, 'web')->put($reply, $answer)->assertForbidden();
        $this->actingAs($instructor, 'web')->put($reply, $answer)
            ->assertRedirect(route('instructor.questions.index'));
        Notification::assertSentTo($student, LessonQuestionAnsweredNotification::class);
        $this->app['auth']->forgetGuards();
        $this->withToken($token);
        $this->getJson($history)->assertOk()
            ->assertJsonCount(1, 'data.questions')
            ->assertJsonPath('data.questions.0.answer', 'Imani ni kumtegemea Mungu.')
            ->assertJsonPath('data.questions.0.lesson_title', 'Biblia')
            ->assertJsonPath('data.next_before_id', null)
            ->assertJsonMissingPath('data.questions.0.user_id');
        $question->refresh()->update(['status' => LessonQuestion::STATUS_PENDING]);
        $this->getJson($history)->assertOk()
            ->assertJsonPath('data.questions.0.status', LessonQuestion::STATUS_ANSWERED);
        $question->update(['status' => LessonQuestion::STATUS_CLOSED]);
        $this->getJson($history)->assertOk()
            ->assertJsonPath('data.questions.0.status', LessonQuestion::STATUS_CLOSED);
        $question->update(['is_published' => false]);
        $this->getJson($history)->assertOk()->assertJsonCount(0, 'data.questions');
        $question->update(['is_published' => true]);

        $this->app['auth']->forgetGuards();
        $this->withToken($other->createToken('Study test', ['study'])->plainTextToken)
            ->getJson($history)->assertOk()->assertJsonCount(0, 'data.questions');
    }

    public function test_an_unassigned_question_does_not_notify_a_legacy_instructor_without_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $legacy = User::factory()->create(['role' => 'instructor']);
        $followUp = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create([
            'title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true, 'instructor_id' => $legacy->id,
        ]);
        $lesson->followUpInstructors()->attach($followUp->id);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $this->topic($module, 'imani');
        LessonEnrollment::create(['user_id' => $student->id, 'lesson_id' => $lesson->id]);

        $this->withToken($student->createToken('Study test', ['study'])->plainTextToken)
            ->postJson('/api/mobile/v1/study/lessons/biblia/topics/imani/questions', [
                'question' => 'Naomba ufafanuzi kuhusu imani.',
            ])->assertCreated();
        Notification::assertSentTo($admin, LessonQuestionAskedNotification::class);
        Notification::assertNotSentTo($legacy, LessonQuestionAskedNotification::class);
        Notification::assertNotSentTo($followUp, LessonQuestionAskedNotification::class);

        LessonQuestion::query()->sole()->forceFill(['created_at' => now()->subHours(73)])->save();
        $this->artisan('lessons:send-question-escalations')->assertSuccessful();
        Notification::assertSentTo($admin, LessonQuestionEscalatedNotification::class);
        Notification::assertNotSentTo($legacy, LessonQuestionEscalatedNotification::class);
        Notification::assertNotSentTo($legacy, LessonQuestionReminderNotification::class);
    }

    public function test_web_questions_keep_the_topic_and_reject_a_topic_from_another_course(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lesson = Lesson::create(['title' => 'Biblia', 'slug' => 'biblia', 'is_published' => true]);
        $module = $lesson->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $topic = $this->topic($module, 'imani');
        $other = Lesson::create(['title' => 'Maombi', 'slug' => 'maombi', 'is_published' => true]);
        $otherModule = $other->modules()->create(['title' => 'Kwanza', 'order' => 1, 'is_published' => true]);
        $otherTopic = $this->topic($otherModule, 'ombi');
        LessonEnrollment::create(['user_id' => $student->id, 'lesson_id' => $lesson->id]);

        $route = route('lessons.questions.store', ['lesson' => $lesson->slug]);
        $this->actingAs($student)->post($route, [
            'question' => 'Naomba ufafanuzi kuhusu imani.',
            'lesson_topic_id' => $otherTopic->id,
        ])->assertSessionHasErrors('lesson_topic_id');
        $this->assertDatabaseCount('lesson_questions', 0);

        $this->actingAs($student)->post($route, [
            'question' => 'Naomba ufafanuzi kuhusu imani.',
            'lesson_topic_id' => $topic->id,
        ])->assertSessionHas('success');
        $this->assertSame($topic->id, LessonQuestion::query()->sole()->lesson_topic_id);
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
