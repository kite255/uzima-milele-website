<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->text('question');

            // 🔥 ADD THIS
            $table->enum('type', ['multiple_choice', 'true_false'])
                ->default('multiple_choice');

            // 🔥 FOR TRUE/FALSE
            $table->string('correct_answer')->nullable();

            // 🔥 FOR MULTIPLE CHOICE (store options JSON)
            $table->json('options')->nullable();

            // 🔥 EXTRA
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};