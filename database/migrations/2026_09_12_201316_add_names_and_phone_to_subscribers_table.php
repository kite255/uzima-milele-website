<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add subscriber name fields
        |--------------------------------------------------------------------------
        |
        | The original email_subscribers migration already contains:
        | - name
        | - email
        | - phone
        |
        | We only add first_name and last_name here.
        |
        */

        if (! Schema::hasColumn('email_subscribers', 'first_name')) {
            Schema::table('email_subscribers', function (Blueprint $table) {
                $table->string('first_name')
                    ->nullable()
                    ->after('id');
            });
        }

        if (! Schema::hasColumn('email_subscribers', 'last_name')) {
            Schema::table('email_subscribers', function (Blueprint $table) {
                $table->string('last_name')
                    ->nullable()
                    ->after('first_name');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Safety checks
        |--------------------------------------------------------------------------
        |
        | These columns already exist in the original migration, but these
        | checks keep this migration safe for environments created from an
        | older database structure.
        |
        */

        if (! Schema::hasColumn('email_subscribers', 'name')) {
            Schema::table('email_subscribers', function (Blueprint $table) {
                $table->string('name')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('email_subscribers', 'phone')) {
            Schema::table('email_subscribers', function (Blueprint $table) {
                $table->string('phone')
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Only remove columns introduced by this migration
        |--------------------------------------------------------------------------
        |
        | Do not remove name or phone because they belong to the original
        | email_subscribers migration.
        |
        */

        if (Schema::hasColumn('email_subscribers', 'first_name')) {
            Schema::table('email_subscribers', function (Blueprint $table) {
                $table->dropColumn('first_name');
            });
        }

        if (Schema::hasColumn('email_subscribers', 'last_name')) {
            Schema::table('email_subscribers', function (Blueprint $table) {
                $table->dropColumn('last_name');
            });
        }
    }
};