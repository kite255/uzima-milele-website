<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_topic_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('lesson_topic_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamp('last_viewed_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'lesson_topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_topic_views');
    }
};