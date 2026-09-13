<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Campaign
            |--------------------------------------------------------------------------
            */
            $table->foreignId('email_campaign_id')
                ->constrained('email_campaigns')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Original Subscriber
            |--------------------------------------------------------------------------
            |
            | Nullable so campaign history remains available even if the subscriber
            | is deleted later.
            |
            */
            $table->foreignId('email_subscriber_id')
                ->nullable()
                ->constrained('email_subscribers')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Recipient Snapshot
            |--------------------------------------------------------------------------
            |
            | Keep the exact name and email address used by this campaign.
            |
            */
            $table->string('name')->nullable();

            $table->string('email');

            /*
            |--------------------------------------------------------------------------
            | Delivery Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'sent',
                'failed',
            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Delivery Tracking
            |--------------------------------------------------------------------------
            */
            $table->timestamp('sent_at')->nullable();

            $table->timestamp('failed_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Error Details
            |--------------------------------------------------------------------------
            */
            $table->text('error_message')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Constraints & Indexes
            |--------------------------------------------------------------------------
            |
            | Prevent the same email address from being added more than once
            | to the same campaign.
            |
            */
            $table->unique(
                ['email_campaign_id', 'email'],
                'campaign_recipient_email_unique'
            );

            $table->index(
                ['email_campaign_id', 'status'],
                'campaign_recipient_status_index'
            );

            $table->index(
                'email_subscriber_id',
                'campaign_recipient_subscriber_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};