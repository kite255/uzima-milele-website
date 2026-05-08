<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'lesson_id')) {
                try {
                    $table->dropForeign(['lesson_id']);
                } catch (\Throwable $e) {
                    //
                }

                $table->dropColumn('lesson_id');
            }

            if (!Schema::hasColumn('quizzes', 'lesson_topic_id')) {
                $table->foreignId('lesson_topic_id')
                    ->after('id')
                    ->constrained('lesson_topics')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('quizzes', 'pass_mark')) {
                $table->integer('pass_mark')->default(70)->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'lesson_topic_id')) {
                try {
                    $table->dropForeign(['lesson_topic_id']);
                } catch (\Throwable $e) {
                    //
                }

                $table->dropColumn('lesson_topic_id');
            }

            if (!Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->foreignId('lesson_id')
                    ->nullable()
                    ->constrained('lessons')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('quizzes', 'pass_mark')) {
                $table->dropColumn('pass_mark');
            }
        });
    }
};