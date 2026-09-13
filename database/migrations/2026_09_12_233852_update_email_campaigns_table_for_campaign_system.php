<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {

            if (! Schema::hasColumn('email_campaigns', 'type')) {
                $table->enum('type', [
                    'devotion',
                    'custom',
                ])
                    ->default('devotion')
                    ->after('name');
            }

            if (! Schema::hasColumn('email_campaigns', 'content')) {
                $table->longText('content')
                    ->nullable()
                    ->after('subject');
            }

            if (! Schema::hasColumn('email_campaigns', 'recipient_scope')) {
                $table->enum('recipient_scope', [
                    'subscribed',
                ])
                    ->default('subscribed')
                    ->after('content');
            }

            if (! Schema::hasColumn('email_campaigns', 'total_recipients')) {
                $table->unsignedInteger('total_recipients')
                    ->default(0)
                    ->after('status');
            }

            if (! Schema::hasColumn('email_campaigns', 'queued_at')) {
                $table->timestamp('queued_at')
                    ->nullable()
                    ->after('failed_count');
            }

            if (! Schema::hasColumn('email_campaigns', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('sent_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Copy old recipient_count into total_recipients
        |--------------------------------------------------------------------------
        */
        if (
            Schema::hasColumn('email_campaigns', 'recipient_count') &&
            Schema::hasColumn('email_campaigns', 'total_recipients')
        ) {
            DB::table('email_campaigns')
                ->update([
                    'total_recipients' => DB::raw('recipient_count'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {

            if (Schema::hasColumn('email_campaigns', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('email_campaigns', 'queued_at')) {
                $table->dropColumn('queued_at');
            }

            if (Schema::hasColumn('email_campaigns', 'total_recipients')) {
                $table->dropColumn('total_recipients');
            }

            if (Schema::hasColumn('email_campaigns', 'recipient_scope')) {
                $table->dropColumn('recipient_scope');
            }

            if (Schema::hasColumn('email_campaigns', 'content')) {
                $table->dropColumn('content');
            }

            if (Schema::hasColumn('email_campaigns', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};