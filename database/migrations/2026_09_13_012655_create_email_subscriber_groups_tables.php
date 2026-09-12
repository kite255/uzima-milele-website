<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_subscriber_groups', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique('name');
        });

        Schema::create('email_subscriber_group_members', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('email_subscriber_group_id')
                ->constrained('email_subscriber_groups')
                ->cascadeOnDelete();

            $table->foreignId('email_subscriber_id')
                ->constrained('email_subscribers')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                [
                    'email_subscriber_group_id',
                    'email_subscriber_id',
                ],
                'email_subscriber_group_member_unique'
            );

            $table->index(
                'email_subscriber_id',
                'email_subscriber_group_member_subscriber_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'email_subscriber_group_members'
        );

        Schema::dropIfExists(
            'email_subscriber_groups'
        );
    }
};