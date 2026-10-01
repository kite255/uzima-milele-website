<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_questions', function (Blueprint $table) {
            $table->timestamp('instructor_reminded_at')->nullable();
            $table->timestamp('lead_escalated_at')->nullable();
            $table->timestamp('admin_escalated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lesson_questions', function (Blueprint $table) {
            $table->dropColumn([
                'instructor_reminded_at',
                'lead_escalated_at',
                'admin_escalated_at',
            ]);
        });
    }
};
