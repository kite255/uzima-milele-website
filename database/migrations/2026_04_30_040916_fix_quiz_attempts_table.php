<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {

            if (!Schema::hasColumn('quiz_attempts', 'user_id')) {
                $table->foreignId('user_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('quiz_attempts', 'quiz_id')) {
                $table->foreignId('quiz_id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('quiz_attempts', 'score')) {
                $table->unsignedInteger('score')->default(0);
            }

            if (!Schema::hasColumn('quiz_attempts', 'correct_answers')) {
                $table->unsignedInteger('correct_answers')->default(0);
            }

            if (!Schema::hasColumn('quiz_attempts', 'total_questions')) {
                $table->unsignedInteger('total_questions')->default(0);
            }

            if (!Schema::hasColumn('quiz_attempts', 'passed')) {
                $table->boolean('passed')->default(false);
            }
        });
    }

    public function down(): void
    {
        // leave empty (safe)
    }
};