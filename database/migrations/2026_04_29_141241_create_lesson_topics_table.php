<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_topics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->string('pdf')->nullable();

            $table->integer('order')->default(1);

            $table->boolean('is_free')->default(false);
            $table->boolean('is_published')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_topics');
    }
};