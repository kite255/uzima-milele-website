<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE email_campaigns
            MODIFY recipient_scope
            ENUM('subscribed', 'selected')
            NOT NULL
            DEFAULT 'subscribed'
        ");

        if (! Schema::hasTable('email_campaign_targets')) {
            Schema::create('email_campaign_targets', function (Blueprint $table): void {
                $table->id();

                $table->foreignId('email_campaign_id')
                    ->constrained('email_campaigns')
                    ->cascadeOnDelete();

                $table->foreignId('email_subscriber_id')
                    ->constrained('email_subscribers')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique(
                    ['email_campaign_id', 'email_subscriber_id'],
                    'email_campaign_target_unique'
                );

                $table->index(
                    'email_subscriber_id',
                    'email_campaign_target_subscriber_index'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_targets');

        DB::table('email_campaigns')
            ->where('recipient_scope', 'selected')
            ->update([
                'recipient_scope' => 'subscribed',
            ]);

        DB::statement("
            ALTER TABLE email_campaigns
            MODIFY recipient_scope
            ENUM('subscribed')
            NOT NULL
            DEFAULT 'subscribed'
        ");
    }
};