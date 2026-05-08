<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (! Schema::hasColumn('questions', 'order')) {
                $table->integer('order')->default(1)->after('correct_answer');
            }

            if (! Schema::hasColumn('questions', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'is_published')) {
                $table->dropColumn('is_published');
            }

            if (Schema::hasColumn('questions', 'order')) {
                $table->dropColumn('order');
            }
        });
    }
};