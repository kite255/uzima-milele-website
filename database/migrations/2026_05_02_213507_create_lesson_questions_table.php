<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('question');

            $table->text('answer')->nullable();

            $table->foreignId('answered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('answered_at')->nullable();

            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->index(['lesson_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_questions');
    }
};