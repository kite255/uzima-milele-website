<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->foreignId('email_campaign_recipient_id')->constrained('email_campaign_recipients')->cascadeOnDelete();
            $table->text('url');
            $table->timestamp('clicked_at');
            $table->text('user_agent')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['email_campaign_id', 'clicked_at']);
            $table->index(['email_campaign_recipient_id', 'clicked_at'], 'campaign_click_recipient_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_clicks');
    }
};
