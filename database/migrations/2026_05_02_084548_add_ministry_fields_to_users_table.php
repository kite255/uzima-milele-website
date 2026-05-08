<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'ministry_name')) {
                $table->string('ministry_name')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'ministry_bio')) {
                $table->text('ministry_bio')->nullable()->after('ministry_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ministry_bio')) {
                $table->dropColumn('ministry_bio');
            }

            if (Schema::hasColumn('users', 'ministry_name')) {
                $table->dropColumn('ministry_name');
            }
        });
    }
};