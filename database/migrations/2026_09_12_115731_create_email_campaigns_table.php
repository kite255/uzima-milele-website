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
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Basic Campaign Information
            |--------------------------------------------------------------------------
            */
            $table->string('name');

            $table->enum('type', [
                'devotion',
                'custom',
            ])->default('devotion');

            /*
            |--------------------------------------------------------------------------
            | Devotion
            |--------------------------------------------------------------------------
            |
            | Only used when campaign type is "devotion".
            |
            */
            $table->foreignId('devotion_id')
                ->nullable()
                ->constrained('devotions')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Email Content
            |--------------------------------------------------------------------------
            */
            $table->string('subject');

            $table->longText('content')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Recipient Scope
            |--------------------------------------------------------------------------
            |
            | Phase 1 sends campaigns to active subscribers.
            |
            */
            $table->enum('recipient_scope', [
                'subscribed',
            ])->default('subscribed');

            /*
            |--------------------------------------------------------------------------
            | Campaign Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'draft',
                'queued',
                'sending',
                'sent',
                'failed',
            ])->default('draft');

            /*
            |--------------------------------------------------------------------------
            | Delivery Statistics
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('total_recipients')
                ->default(0);

            $table->unsignedInteger('sent_count')
                ->default(0);

            $table->unsignedInteger('failed_count')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Delivery Times
            |--------------------------------------------------------------------------
            */
            $table->timestamp('queued_at')
                ->nullable();

            $table->timestamp('sent_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Creator
            |--------------------------------------------------------------------------
            |
            | Preserve campaign history even if the admin user is deleted.
            |
            */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index(
                'status',
                'email_campaign_status_index'
            );

            $table->index(
                'type',
                'email_campaign_type_index'
            );

            $table->index(
                'created_by',
                'email_campaign_creator_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};