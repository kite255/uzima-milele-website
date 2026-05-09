<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'recommended_study_pace')) {
                $table->string('recommended_study_pace')
                    ->nullable()
                    ->after('estimated_duration_minutes');
            }

            if (! Schema::hasColumn('lessons', 'min_completion_days')) {
                $table->unsignedInteger('min_completion_days')
                    ->nullable()
                    ->after('recommended_study_pace');
            }

            if (! Schema::hasColumn('lessons', 'max_completion_days')) {
                $table->unsignedInteger('max_completion_days')
                    ->nullable()
                    ->after('min_completion_days');
            }

            if (! Schema::hasColumn('lessons', 'course_deadline')) {
                $table->date('course_deadline')
                    ->nullable()
                    ->after('max_completion_days');
            }

            if (! Schema::hasColumn('lessons', 'allow_schedule_reset')) {
                $table->boolean('allow_schedule_reset')
                    ->default(true)
                    ->after('course_deadline');
            }

            if (! Schema::hasColumn('lessons', 'reminder_days_before_deadline')) {
                $table->unsignedInteger('reminder_days_before_deadline')
                    ->nullable()
                    ->after('allow_schedule_reset');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $columns = [
                'recommended_study_pace',
                'min_completion_days',
                'max_completion_days',
                'course_deadline',
                'allow_schedule_reset',
                'reminder_days_before_deadline',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('lessons', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};