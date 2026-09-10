<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            $table->string('follow_up_status')
                ->default('not_contacted')
                ->after('instructor_assigned_at');

            $table->timestamp('next_follow_up_at')
                ->nullable()
                ->after('follow_up_status');

            $table->timestamp('last_follow_up_at')
                ->nullable()
                ->after('next_follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'follow_up_status',
                'next_follow_up_at',
                'last_follow_up_at',
            ]);
        });
    }
};