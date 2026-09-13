<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('email_campaigns', 'email_subscriber_group_id')) {
            Schema::table('email_campaigns', function (Blueprint $table): void {
                $table->foreignId('email_subscriber_group_id')
                    ->nullable()
                    ->after('recipient_scope')
                    ->constrained('email_subscriber_groups')
                    ->nullOnDelete();
            });
        }

        DB::statement("
            ALTER TABLE email_campaigns
            MODIFY recipient_scope
            ENUM('subscribed', 'selected', 'group')
            NOT NULL
            DEFAULT 'subscribed'
        ");
    }

    public function down(): void
    {
        DB::table('email_campaigns')
            ->where('recipient_scope', 'group')
            ->update([
                'recipient_scope' => 'subscribed',
                'email_subscriber_group_id' => null,
            ]);

        DB::statement("
            ALTER TABLE email_campaigns
            MODIFY recipient_scope
            ENUM('subscribed', 'selected')
            NOT NULL
            DEFAULT 'subscribed'
        ");

        if (Schema::hasColumn('email_campaigns', 'email_subscriber_group_id')) {
            Schema::table('email_campaigns', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('email_subscriber_group_id');
            });
        }
    }
};