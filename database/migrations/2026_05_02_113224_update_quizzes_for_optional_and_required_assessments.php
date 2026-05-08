<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (! Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('quizzes', 'module_id')) {
                $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            }

            if (! Schema::hasColumn('quizzes', 'quiz_type')) {
                $table->string('quiz_type')->default('kujipima')->after('title');
            }

            if (! Schema::hasColumn('quizzes', 'description')) {
                $table->text('description')->nullable()->after('quiz_type');
            }

            if (! Schema::hasColumn('quizzes', 'pass_mark')) {
                $table->unsignedTinyInteger('pass_mark')->default(70)->after('description');
            }

            if (! Schema::hasColumn('quizzes', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('pass_mark');
            }

            if (! Schema::hasColumn('quizzes', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('is_required');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'module_id')) {
                $table->dropConstrainedForeignId('module_id');
            }

            if (Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->dropConstrainedForeignId('lesson_id');
            }

            if (Schema::hasColumn('quizzes', 'quiz_type')) {
                $table->dropColumn('quiz_type');
            }

            if (Schema::hasColumn('quizzes', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('quizzes', 'pass_mark')) {
                $table->dropColumn('pass_mark');
            }

            if (Schema::hasColumn('quizzes', 'is_required')) {
                $table->dropColumn('is_required');
            }

            if (Schema::hasColumn('quizzes', 'is_published')) {
                $table->dropColumn('is_published');
            }
        });
    }
};