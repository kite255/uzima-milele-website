<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'estimated_duration_minutes')) {
                $table->unsignedInteger('estimated_duration_minutes')
                    ->nullable()
                    ->after('level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'estimated_duration_minutes')) {
                $table->dropColumn('estimated_duration_minutes');
            }
        });
    }
};