<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_results', 'user_name')) {
                $table->string('user_name')->nullable()->after('id');
            }

            if (!Schema::hasColumn('quiz_results', 'lesson_id')) {
                $table->foreignId('lesson_id')
                    ->nullable()
                    ->after('user_name')
                    ->constrained('watoto_videos')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('quiz_results', 'score')) {
                $table->integer('score')->default(0)->after('lesson_id');
            }

            if (!Schema::hasColumn('quiz_results', 'correct')) {
                $table->integer('correct')->default(0)->after('score');
            }

            if (!Schema::hasColumn('quiz_results', 'total')) {
                $table->integer('total')->default(0)->after('correct');
            }

            if (!Schema::hasColumn('quiz_results', 'passed')) {
                $table->boolean('passed')->default(false)->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_results', 'lesson_id')) {
                $table->dropForeign(['lesson_id']);
                $table->dropColumn('lesson_id');
            }

            foreach (['user_name', 'score', 'correct', 'total', 'passed'] as $column) {
                if (Schema::hasColumn('quiz_results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};