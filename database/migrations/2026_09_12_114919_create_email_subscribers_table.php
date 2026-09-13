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
        Schema::create('email_subscribers', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();

            $table->string('email')->unique();

            $table->enum('status', [
                'subscribed',
                'unsubscribed',
            ])->default('subscribed');

            /*
            |--------------------------------------------------------------------------
            | Unsubscribe token
            |--------------------------------------------------------------------------
            | Used for secure unsubscribe links in emails.
            */
            $table->string('unsubscribe_token', 64)->unique();

            /*
            |--------------------------------------------------------------------------
            | Subscription information
            |--------------------------------------------------------------------------
            */
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            | Examples:
            | website
            | admin
            | import
            | registration
            */
            $table->string('source')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Optional metadata
            |--------------------------------------------------------------------------
            */
            $table->string('phone')->nullable();
            $table->string('language', 10)->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_subscribers');
    }
};