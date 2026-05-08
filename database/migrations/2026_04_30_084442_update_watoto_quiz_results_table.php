<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watoto_quiz_results', function (Blueprint $table) {
            if (! Schema::hasColumn('watoto_quiz_results', 'watoto_video_id')) {
                $table->foreignId('watoto_video_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('watoto_videos')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('watoto_quiz_results', 'user_name')) {
                $table->string('user_name')->nullable()->after('watoto_video_id');
            }

            if (! Schema::hasColumn('watoto_quiz_results', 'score')) {
                $table->integer('score')->default(0)->after('user_name');
            }

            if (! Schema::hasColumn('watoto_quiz_results', 'correct')) {
                $table->integer('correct')->default(0)->after('score');
            }

            if (! Schema::hasColumn('watoto_quiz_results', 'total')) {
                $table->integer('total')->default(0)->after('correct');
            }

            if (! Schema::hasColumn('watoto_quiz_results', 'passed')) {
                $table->boolean('passed')->default(false)->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('watoto_quiz_results', function (Blueprint $table) {
            if (Schema::hasColumn('watoto_quiz_results', 'watoto_video_id')) {
                $table->dropForeign(['watoto_video_id']);
                $table->dropColumn('watoto_video_id');
            }

            foreach (['user_name', 'score', 'correct', 'total', 'passed'] as $column) {
                if (Schema::hasColumn('watoto_quiz_results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};