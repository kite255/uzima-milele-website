<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table): void {
            $table->id();

            $table->boolean('auto_schedule_devotions')
                ->default(true);

            $table->string(
                'default_devotion_send_time',
                5
            )->default('06:00');

            $table->string(
                'default_recipient_scope',
                50
            )->default('subscribed');

            $table->foreignId(
                'email_subscriber_group_id'
            )
                ->nullable()
                ->constrained(
                    'email_subscriber_groups'
                )
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'email_settings'
        );
    }
};