<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->timestamp('suppressed_at')->nullable()->after('failed_at');
            $table->string('suppression_reason')->nullable()->after('suppressed_at');
            $table->timestamp('first_clicked_at')->nullable()->after('last_opened_at');
            $table->timestamp('last_clicked_at')->nullable()->after('first_clicked_at');
            $table->unsignedInteger('click_count')->default(0)->after('last_clicked_at');
            $table->timestamp('unsubscribed_at')->nullable()->after('click_count');
            $table->unsignedInteger('failure_count')->default(0)->after('unsubscribed_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->dropColumn([
                'suppressed_at',
                'suppression_reason',
                'first_clicked_at',
                'last_clicked_at',
                'click_count',
                'unsubscribed_at',
                'failure_count',
            ]);
        });
    }
};
