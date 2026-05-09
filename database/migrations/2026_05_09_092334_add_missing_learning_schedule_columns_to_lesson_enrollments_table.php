<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('lesson_enrollments', 'study_pace')) {
                $table->string('study_pace')->nullable()->after('enrolled_at');
            }

            if (! Schema::hasColumn('lesson_enrollments', 'study_hours_per_week')) {
                $table->unsignedInteger('study_hours_per_week')->nullable()->after('study_pace');
            }

            if (! Schema::hasColumn('lesson_enrollments', 'target_completion_date')) {
                $table->timestamp('target_completion_date')->nullable()->after('study_hours_per_week');
            }

            if (! Schema::hasColumn('lesson_enrollments', 'schedule_started_at')) {
                $table->timestamp('schedule_started_at')->nullable()->after('target_completion_date');
            }

            if (! Schema::hasColumn('lesson_enrollments', 'schedule_updated_at')) {
                $table->timestamp('schedule_updated_at')->nullable()->after('schedule_started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_enrollments', 'schedule_updated_at')) {
                $table->dropColumn('schedule_updated_at');
            }

            if (Schema::hasColumn('lesson_enrollments', 'schedule_started_at')) {
                $table->dropColumn('schedule_started_at');
            }

            if (Schema::hasColumn('lesson_enrollments', 'target_completion_date')) {
                $table->dropColumn('target_completion_date');
            }

            if (Schema::hasColumn('lesson_enrollments', 'study_hours_per_week')) {
                $table->dropColumn('study_hours_per_week');
            }

            if (Schema::hasColumn('lesson_enrollments', 'study_pace')) {
                $table->dropColumn('study_pace');
            }
        });
    }
};