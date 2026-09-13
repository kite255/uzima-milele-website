<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('email_campaigns', 'scheduled_at')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                $table->timestamp('scheduled_at')
                    ->nullable()
                    ->after('failed_count');

                $table->index(
                    'scheduled_at',
                    'email_campaign_scheduled_at_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('email_campaigns', 'scheduled_at')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                $table->dropIndex(
                    'email_campaign_scheduled_at_index'
                );

                $table->dropColumn('scheduled_at');
            });
        }
    }
};