<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear old invalid references first
        DB::table('lessons')->update([
            'instructor_id' => null,
        ]);

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign('lessons_instructor_id_foreign');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('instructor_id')
                ->references('id')
                ->on('instructors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign('lessons_instructor_id_foreign');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('instructor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};