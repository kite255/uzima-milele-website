<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watoto_quizzes', function (Blueprint $table) {
            $table->string('type')->default('mcq')->after('question');
        });
    }

    public function down(): void
    {
        Schema::table('watoto_quizzes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};