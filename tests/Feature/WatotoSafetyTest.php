<?php

namespace Tests\Feature;

use App\Models\WatotoVideo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Uses a disposable in-memory SQLite schema. Never run against a live database.
 * DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=WatotoSafetyTest
 */
class WatotoSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('DB_CONNECTION') !== 'sqlite' || getenv('DB_DATABASE') !== ':memory:') {
            $this->markTestSkipped('Run only with DB_CONNECTION=sqlite DB_DATABASE=:memory:.');
        }

        parent::setUp();

        Schema::create('watoto_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('youtube_url');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
        Schema::create('watoto_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watoto_video_id');
            $table->string('question');
            $table->string('correct_answer');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('watoto_quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watoto_video_id');
            $table->string('user_name')->nullable();
            $table->integer('score');
            $table->integer('correct');
            $table->integer('total');
            $table->boolean('passed');
            $table->timestamps();
        });
    }

    public function test_youtube_embed_accepts_only_approved_video_hosts(): void
    {
        $id = 'dQw4w9WgXcQ';
        $this->assertSame('https://www.youtube-nocookie.com/embed/'.$id, (new WatotoVideo([
            'youtube_url' => 'https://www.youtube.com/watch?v='.$id,
        ]))->youtube_embed);
        $this->assertSame($id, WatotoVideo::youtubeVideoId('https://youtu.be/'.$id));
        $this->assertSame($id, WatotoVideo::youtubeVideoId('https://www.youtube.com/shorts/'.$id));
        $this->assertNull(WatotoVideo::youtubeVideoId('https://youtube.com.evil.example/watch?v='.$id));
        $this->assertNull(WatotoVideo::youtubeVideoId('http://youtube.com/watch?v='.$id));
        $this->assertNull(WatotoVideo::youtubeVideoId('https://evil.example/embed/'.$id));
    }

    public function test_valid_quiz_displays_correct_score_and_stores_no_name(): void
    {
        [$videoId, $firstId, $secondId] = $this->seedQuiz();

        $this->withSession(['watoto_quiz_questions' => [
            $videoId => [(string) $firstId, (string) $secondId],
        ]])->post(route('children.quiz.submit', 'watoto-test'), [
            'answers' => [(string) $firstId => 'A', (string) $secondId => 'A'],
        ])->assertOk()->assertSee('50%')->assertSee('1 / 2');

        $this->assertDatabaseHas('watoto_quiz_results', [
            'watoto_video_id' => $videoId,
            'user_name' => 'Guest',
            'score' => 50,
            'correct' => 1,
            'total' => 2,
        ]);
    }

    public function test_forged_quiz_ids_do_not_save_results(): void
    {
        [$videoId, $firstId] = $this->seedQuiz();

        $this->withSession(['watoto_quiz_questions' => [
            $videoId => [(string) $firstId],
        ]])->from(route('children.show', 'watoto-test'))
            ->post(route('children.quiz.submit', 'watoto-test'), [
                'answers' => ['99999' => 'A'],
            ])->assertRedirect(route('children.show', 'watoto-test'))
              ->assertSessionHasErrors('answers');

        $this->assertDatabaseCount('watoto_quiz_results', 0);
    }

    public function test_invalid_answer_does_not_save_result(): void
    {
        [$videoId, $firstId] = $this->seedQuiz();

        $this->withSession(['watoto_quiz_questions' => [
            $videoId => [(string) $firstId],
        ]])->post(route('children.quiz.submit', 'watoto-test'), [
            'answers' => [(string) $firstId => 'E'],
        ])->assertSessionHasErrors('answers.'.$firstId);

        $this->assertDatabaseCount('watoto_quiz_results', 0);
    }

    private function seedQuiz(): array
    {
        $videoId = DB::table('watoto_videos')->insertGetId([
            'title' => 'Watoto Test', 'slug' => 'watoto-test',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'is_published' => true,
        ]);
        $firstId = DB::table('watoto_quizzes')->insertGetId([
            'watoto_video_id' => $videoId, 'question' => 'Swali 1',
            'correct_answer' => 'A', 'is_active' => true,
        ]);
        $secondId = DB::table('watoto_quizzes')->insertGetId([
            'watoto_video_id' => $videoId, 'question' => 'Swali 2',
            'correct_answer' => 'B', 'is_active' => true,
        ]);

        return [$videoId, $firstId, $secondId];
    }
}
