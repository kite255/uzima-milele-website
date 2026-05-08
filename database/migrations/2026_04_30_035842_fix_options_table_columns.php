<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('options', function (Blueprint $table) {
            if (!Schema::hasColumn('options', 'question_id')) {
                $table->foreignId('question_id')
                    ->after('id')
                    ->constrained('questions')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('options', 'option_text')) {
                $table->string('option_text')->after('question_id');
            }

            if (!Schema::hasColumn('options', 'is_correct')) {
                $table->boolean('is_correct')->default(false)->after('option_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            if (Schema::hasColumn('options', 'question_id')) {
                $table->dropForeign(['question_id']);
                $table->dropColumn('question_id');
            }

            if (Schema::hasColumn('options', 'option_text')) {
                $table->dropColumn('option_text');
            }

            if (Schema::hasColumn('options', 'is_correct')) {
                $table->dropColumn('is_correct');
            }
        });
    }
};