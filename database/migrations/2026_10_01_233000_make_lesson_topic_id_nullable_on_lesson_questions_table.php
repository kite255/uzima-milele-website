<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lesson_questions') || ! Schema::hasColumn('lesson_questions', 'lesson_topic_id')) {
            return;
        }

        Schema::table('lesson_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_topic_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lesson_questions') || ! Schema::hasColumn('lesson_questions', 'lesson_topic_id')) {
            return;
        }

        if (DB::table('lesson_questions')->whereNull('lesson_topic_id')->exists()) {
            return;
        }

        Schema::table('lesson_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_topic_id')
                ->nullable(false)
                ->change();
        });
    }
};
