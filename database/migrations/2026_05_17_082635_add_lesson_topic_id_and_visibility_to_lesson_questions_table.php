<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_questions', function (Blueprint $table) {
            if (! Schema::hasColumn('lesson_questions', 'lesson_topic_id')) {
                $table->foreignId('lesson_topic_id')
                    ->nullable()
                    ->after('lesson_id')
                    ->constrained('lesson_topics')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('lesson_questions', 'status')) {
                $table->string('status')
                    ->default('pending')
                    ->after('question');
            }

            if (! Schema::hasColumn('lesson_questions', 'visibility')) {
                $table->string('visibility')
                    ->default('private')
                    ->after('status');
            }

            if (! Schema::hasColumn('lesson_questions', 'answer')) {
                $table->longText('answer')
                    ->nullable()
                    ->after('question');
            }

            if (! Schema::hasColumn('lesson_questions', 'answered_by')) {
                $table->foreignId('answered_by')
                    ->nullable()
                    ->after('answer')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('lesson_questions', 'answered_at')) {
                $table->timestamp('answered_at')
                    ->nullable()
                    ->after('answered_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lesson_questions', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_questions', 'lesson_topic_id')) {
                $table->dropConstrainedForeignId('lesson_topic_id');
            }

            if (Schema::hasColumn('lesson_questions', 'answered_by')) {
                $table->dropConstrainedForeignId('answered_by');
            }

            if (Schema::hasColumn('lesson_questions', 'answered_at')) {
                $table->dropColumn('answered_at');
            }

            if (Schema::hasColumn('lesson_questions', 'visibility')) {
                $table->dropColumn('visibility');
            }

            if (Schema::hasColumn('lesson_questions', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('lesson_questions', 'answer')) {
                $table->dropColumn('answer');
            }
        });
    }
};