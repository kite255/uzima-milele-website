<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLessonsTableFull extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {

            if (!Schema::hasColumn('lessons', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('description');
            }

            if (!Schema::hasColumn('lessons', 'video_url')) {
                $table->string('video_url')->nullable()->after('cover_image');
            }

            if (!Schema::hasColumn('lessons', 'category')) {
                $table->string('category')->nullable();
            }

            if (!Schema::hasColumn('lessons', 'level')) {
                $table->string('level')->nullable();
            }

            if (!Schema::hasColumn('lessons', 'is_published')) {
                $table->boolean('is_published')->default(false);
            }

            if (!Schema::hasColumn('lessons', 'content')) {
                $table->longText('content')->nullable();
            }

            if (!Schema::hasColumn('lessons', 'instructor_id')) {
                $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            }

        });
    }

    public function down(): void
    {
        // optional rollback
    }
}