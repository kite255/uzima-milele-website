<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lessons', 'instructor_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->foreignId('instructor_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('instructors')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lessons', 'instructor_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropConstrainedForeignId('instructor_id');
            });
        }
    }
};