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
                'draft',
                'scheduled',
                'queued',
                'sending',
                'sent',
                'failed'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Protect rollback
        |--------------------------------------------------------------------------
        |
        | scheduled does not exist in the previous enum, so convert any
        | scheduled campaign back to draft before restoring the old enum.
        |
        */

        DB::table('email_campaigns')
            ->where('status', 'scheduled')
            ->update([
                'status' => 'draft',
            ]);

        DB::statement("
            ALTER TABLE email_campaigns
            MODIFY COLUMN status ENUM(
                'draft',
                'queued',
                'sending',
                'sent',
                'failed'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};