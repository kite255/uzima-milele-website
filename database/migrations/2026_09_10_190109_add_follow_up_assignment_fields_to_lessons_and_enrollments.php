<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'lead_instructor_id')) {
                $table->foreignId('lead_instructor_id')
                    ->nullable()
                    ->after('instructor_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('lessons', 'lead_can_receive_students')) {
                $table->boolean('lead_can_receive_students')
                    ->default(false)
                    ->after('lead_instructor_id');
            }
        });

        Schema::table('lesson_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('lesson_enrollments', 'follow_up_instructor_id')) {
                $table->foreignId('follow_up_instructor_id')
                    ->nullable()
                    ->after('lesson_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('lesson_enrollments', 'instructor_assigned_at')) {
                $table->timestamp('instructor_assigned_at')
                    ->nullable()
                    ->after('follow_up_instructor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_enrollments', 'follow_up_instructor_id')) {
                $table->dropConstrainedForeignId('follow_up_instructor_id');
            }

            if (Schema::hasColumn('lesson_enrollments', 'instructor_assigned_at')) {
                $table->dropColumn('instructor_assigned_at');
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'lead_instructor_id')) {
                $table->dropConstrainedForeignId('lead_instructor_id');
            }

            if (Schema::hasColumn('lessons', 'lead_can_receive_students')) {
                $table->dropColumn('lead_can_receive_students');
            }
        });
    }
};