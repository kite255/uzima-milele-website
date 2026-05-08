<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (! Schema::hasColumn('questions', 'type')) {
                $table->enum('type', ['multiple_choice', 'true_false'])
                    ->default('multiple_choice')
                    ->after('quiz_id');
            }

            if (! Schema::hasColumn('questions', 'correct_answer')) {
                $table->string('correct_answer')->nullable()->after('type');
            }

            if (! Schema::hasColumn('questions', 'options')) {
                $table->json('options')->nullable()->after('correct_answer');
            }

            if (! Schema::hasColumn('questions', 'explanation')) {
                $table->text('explanation')->nullable()->after('options');
            }

            if (! Schema::hasColumn('questions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('explanation');
            }

            if (! Schema::hasColumn('questions', 'sort_order')) {
                $table->integer('sort_order')->default(1)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('questions', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('questions', 'explanation')) {
                $table->dropColumn('explanation');
            }

            if (Schema::hasColumn('questions', 'options')) {
                $table->dropColumn('options');
            }

            if (Schema::hasColumn('questions', 'correct_answer')) {
                $table->dropColumn('correct_answer');
            }

            if (Schema::hasColumn('questions', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};