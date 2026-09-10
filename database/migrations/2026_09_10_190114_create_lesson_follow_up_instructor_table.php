<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lesson_follow_up_instructor')) {
            Schema::create('lesson_follow_up_instructor', function (Blueprint $table) {
                $table->id();

                $table->foreignId('lesson_id')
                    ->constrained('lessons')
                    ->cascadeOnDelete();

                $table->foreignId('instructor_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique([
                    'lesson_id',
                    'instructor_id',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_follow_up_instructor');
    }
};