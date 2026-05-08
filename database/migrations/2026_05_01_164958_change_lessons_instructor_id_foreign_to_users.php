<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign('lessons_instructor_id_foreign');
        });

        DB::table('lessons')->update([
            'instructor_id' => null,
        ]);

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('instructor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign('lessons_instructor_id_foreign');
        });

        DB::table('lessons')->update([
            'instructor_id' => null,
        ]);

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('instructor_id')
                ->references('id')
                ->on('instructors')
                ->nullOnDelete();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};