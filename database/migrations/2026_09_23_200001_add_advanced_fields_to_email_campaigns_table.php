<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('sent_at');
            $table->timestamp('cancelled_at')->nullable()->after('paused_at');
            $table->timestamp('completed_at')->nullable()->after('cancelled_at');
            $table->foreignId('parent_campaign_id')->nullable()->after('completed_at')
                ->constrained('email_campaigns')->nullOnDelete();
            $table->string('audience_filter_type')->nullable()->after('parent_campaign_id');
            $table->text('audience_filter_value')->nullable()->after('audience_filter_type');
            $table->unsignedBigInteger('template_id')->nullable()->after('audience_filter_value');
            $table->timestamp('last_batch_sent_at')->nullable()->after('template_id');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropForeign(['parent_campaign_id']);
            $table->dropColumn([
                'paused_at',
                'cancelled_at',
                'completed_at',
                'parent_campaign_id',
                'audience_filter_type',
                'audience_filter_value',
                'template_id',
                'last_batch_sent_at',
            ]);
        });
    }
};
