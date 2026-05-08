<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watoto_videos', function (Blueprint $table) {
            $table->text('main_lesson')->nullable()->change();
            $table->text('bible_verse')->nullable()->change();
            $table->text('reflection_question')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('watoto_videos', function (Blueprint $table) {
            $table->string('main_lesson')->nullable()->change();
            $table->string('bible_verse')->nullable()->change();
            $table->string('reflection_question')->nullable()->change();
        });
    }
};