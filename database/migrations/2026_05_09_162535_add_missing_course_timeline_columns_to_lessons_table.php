<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'recommended_study_pace')) {
                $table->string('recommended_study_pace')->nullable();
            }

            if (! Schema::hasColumn('lessons', 'min_completion_days')) {
                $table->unsignedInteger('min_completion_days')->nullable();
            }

            if (! Schema::hasColumn('lessons', 'max_completion_days')) {
                $table->unsignedInteger('max_completion_days')->nullable();
            }

            if (! Schema::hasColumn('lessons', 'course_deadline')) {
                $table->date('course_deadline')->nullable();
            }

            if (! Schema::hasColumn('lessons', 'allow_schedule_reset')) {
                $table->boolean('allow_schedule_reset')->default(true);
            }

            if (! Schema::hasColumn('lessons', 'reminder_days_before_deadline')) {
                $table->unsignedInteger('reminder_days_before_deadline')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'reminder_days_before_deadline')) {
                $table->dropColumn('reminder_days_before_deadline');
            }

            if (Schema::hasColumn('lessons', 'allow_schedule_reset')) {
                $table->dropColumn('allow_schedule_reset');
            }

            if (Schema::hasColumn('lessons', 'course_deadline')) {
                $table->dropColumn('course_deadline');
            }

            if (Schema::hasColumn('lessons', 'max_completion_days')) {
                $table->dropColumn('max_completion_days');
            }

            if (Schema::hasColumn('lessons', 'min_completion_days')) {
                $table->dropColumn('min_completion_days');
            }

            if (Schema::hasColumn('lessons', 'recommended_study_pace')) {
                $table->dropColumn('recommended_study_pace');
            }
        });
    }
};