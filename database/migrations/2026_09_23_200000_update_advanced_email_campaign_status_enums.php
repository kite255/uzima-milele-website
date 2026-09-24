<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE email_campaigns
            MODIFY COLUMN status ENUM(
                'draft','scheduled','queued','sending','paused','completed','cancelled','sent','failed'
            ) NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            ALTER TABLE email_campaign_recipients
            MODIFY COLUMN status ENUM('pending','sent','failed','suppressed')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::table('email_campaigns')
            ->whereIn('status', ['paused', 'completed', 'cancelled'])
            ->update(['status' => 'draft']);

        DB::table('email_campaign_recipients')
            ->where('status', 'suppressed')
            ->update(['status' => 'failed']);

        DB::statement("
            ALTER TABLE email_campaigns
            MODIFY COLUMN status ENUM('draft','scheduled','queued','sending','sent','failed')
            NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            ALTER TABLE email_campaign_recipients
            MODIFY COLUMN status ENUM('pending','sent','failed')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
