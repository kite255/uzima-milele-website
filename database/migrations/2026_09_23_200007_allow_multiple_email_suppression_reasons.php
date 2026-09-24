<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_suppressions', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['email', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::table('email_suppressions', function (Blueprint $table) {
            $table->dropUnique(['email', 'reason']);
            $table->unique('email');
        });
    }
};
