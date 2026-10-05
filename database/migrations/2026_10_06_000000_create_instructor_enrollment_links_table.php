<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_enrollment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 6)->unique();
            $table->timestamps();

            $table->unique(['lesson_id', 'instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_enrollment_links');
    }
};
