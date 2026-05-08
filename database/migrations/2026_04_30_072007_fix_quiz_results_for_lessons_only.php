<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LESSON QUIZ RESULTS
        Schema::table('quiz_results', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_results', 'lesson_id')) {
                try {
                    $table->dropForeign(['lesson_id']);
                } catch (\Throwable $e) {
                    //
                }

                $table->dropColumn('lesson_id');
            }

            if (! Schema::hasColumn('quiz_results', 'lesson_topic_id')) {
                $table->foreignId('lesson_topic_id')
                    ->nullable()
                    ->after('user_name')
                    ->constrained('lesson_topics')
                    ->cascadeOnDelete();
            }
        });

        // WATOTO QUIZ RESULTS
        if (! Schema::hasTable('watoto_quiz_results')) {
            Schema::create('watoto_quiz_results', function (Blueprint $table) {
                $table->id();

                $table->foreignId('watoto_video_id')
                    ->constrained('watoto_videos')
                    ->cascadeOnDelete();

                $table->string('user_name')->nullable();

                $table->integer('score')->default(0);
                $table->integer('correct')->default(0);
                $table->integer('total')->default(0);
                $table->boolean('passed')->default(false);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('watoto_quiz_results')) {
            Schema::dropIfExists('watoto_quiz_results');
        }

        Schema::table('quiz_results', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_results', 'lesson_topic_id')) {
                try {
                    $table->dropForeign(['lesson_topic_id']);
                } catch (\Throwable $e) {
                    //
                }

                $table->dropColumn('lesson_topic_id');
            }

            if (! Schema::hasColumn('quiz_results', 'lesson_id')) {
                $table->foreignId('lesson_id')
                    ->nullable()
                    ->constrained('watoto_videos')
                    ->cascadeOnDelete();
            }
        });
    }
};