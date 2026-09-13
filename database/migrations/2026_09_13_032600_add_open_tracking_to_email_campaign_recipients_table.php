<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'email_campaign_recipients',
            function (Blueprint $table) {
                $table->string(
                    'tracking_token',
                    64
                )
                    ->nullable()
                    ->unique();

                $table->timestamp(
                    'first_opened_at'
                )->nullable();

                $table->timestamp(
                    'last_opened_at'
                )->nullable();

                $table->unsignedInteger(
                    'open_count'
                )->default(0);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Recipients
        |--------------------------------------------------------------------------
        */
        DB::table('email_campaign_recipients')
            ->whereNull('tracking_token')
            ->orderBy('id')
            ->chunkById(
                500,
                function ($recipients): void {
                    foreach ($recipients as $recipient) {
                        DB::table(
                            'email_campaign_recipients'
                        )
                            ->where(
                                'id',
                                $recipient->id
                            )
                            ->update([
                                'tracking_token' =>
                                    (string) Str::uuid(),
                            ]);
                    }
                }
            );
    }

    public function down(): void
    {
        Schema::table(
            'email_campaign_recipients',
            function (Blueprint $table) {
                $table->dropUnique([
                    'tracking_token',
                ]);

                $table->dropColumn([
                    'tracking_token',
                    'first_opened_at',
                    'last_opened_at',
                    'open_count',
                ]);
            }
        );
    }
};