<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watoto_quizzes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('watoto_video_id')
                ->constrained('watoto_videos')
                ->cascadeOnDelete();

            $table->text('question');

            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d')->nullable();

            $table->string('correct_answer'); // A, B, C, or D

            $table->text('explanation')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watoto_quizzes');
    }
};