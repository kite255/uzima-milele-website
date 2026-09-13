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
        | Compatibility migration only
        |--------------------------------------------------------------------------
        |
        | Fresh installations already have:
        | - name
        | - email
        | - campaign_recipient_email_unique
        |
        | This migration only fills missing snapshot columns for older
        | databases that were created before those fields existed.
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
        | Backfill old recipient rows
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

        /*
        |--------------------------------------------------------------------------
        | No extra index needed
        |--------------------------------------------------------------------------
        |
        | The original table already has:
        |
        | campaign_recipient_email_unique
        |
        | on:
        | email_campaign_id + email
        |
        */
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally empty
        |--------------------------------------------------------------------------
        |
        | Do not drop name/email here because fresh installations create
        | those columns in the original create-table migration.
        |
        */
    }
};