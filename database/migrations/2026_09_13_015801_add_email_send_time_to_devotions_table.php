<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devotions', function (Blueprint $table): void {
            $table->string('email_send_time', 5)
                ->nullable()
                ->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('devotions', function (Blueprint $table): void {
            $table->dropColumn('email_send_time');
        });
    }
};