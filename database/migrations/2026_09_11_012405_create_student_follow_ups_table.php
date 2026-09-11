<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_follow_ups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_enrollment_id')
                ->constrained('lesson_enrollments')
                ->cascadeOnDelete();

            $table->foreignId('instructor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('contact_method')
                ->nullable();

            $table->string('outcome')
                ->nullable();

            $table->text('note');

            $table->timestamp('next_follow_up_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'lesson_enrollment_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_follow_ups');
    }
};