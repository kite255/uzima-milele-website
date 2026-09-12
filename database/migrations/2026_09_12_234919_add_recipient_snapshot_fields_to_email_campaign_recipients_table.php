<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Compatibility Migration
        |--------------------------------------------------------------------------
        |
        | Fresh installations already contain name and email in
        | email_campaign_recipients.
        |
        | This migration only adds them when they are missing, mainly for
        | older databases.
        |
        */

        if (! Schema::hasColumn('email_campaign_recipients', 'name')) {
            Schema::table('email_campaign_recipients', function (Blueprint $table) {
                $table->string('name')
                    ->nullable()
                    ->after('email_subscriber_id');
            });
        }

        if (! Schema::hasColumn('email_campaign_recipients', 'email')) {
            Schema::table('email_campaign_recipients', function (Blueprint $table) {
                $table->string('email')
                    ->nullable()
                    ->after('name');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Backfill Older Records
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn('email_campaign_recipients', 'name')
            && Schema::hasColumn('email_campaign_recipients', 'email')
        ) {
            DB::statement("
                UPDATE email_campaign_recipients AS recipients
                INNER JOIN email_subscribers AS subscribers
                    ON subscribers.id = recipients.email_subscriber_id
                SET
                    recipients.name = COALESCE(
                        recipients.name,
                        subscribers.name,
                        TRIM(
                            CONCAT_WS(
                                ' ',
                                subscribers.first_name,
                                subscribers.last_name
                            )
                        )
                    ),
                    recipients.email = COALESCE(
                        recipients.email,
                        LOWER(TRIM(subscribers.email))
                    )
                WHERE
                    recipients.name IS NULL
                    OR recipients.email IS NULL
            ");
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally Empty
        |--------------------------------------------------------------------------
        |
        | Do not drop name or email because fresh installations create these
        | columns in the original create table migration.
        |
        */
    }
};
