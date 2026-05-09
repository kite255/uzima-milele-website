<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            $table->string('study_pace')->nullable()->after('enrolled_at');
            $table->unsignedInteger('study_hours_per_week')->nullable()->after('study_pace');
            $table->timestamp('target_completion_date')->nullable()->after('study_hours_per_week');
            $table->timestamp('schedule_started_at')->nullable()->after('target_completion_date');
            $table->timestamp('schedule_updated_at')->nullable()->after('schedule_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'study_pace',
                'study_hours_per_week',
                'target_completion_date',
                'schedule_started_at',
                'schedule_updated_at',
            ]);
        });
    }
};