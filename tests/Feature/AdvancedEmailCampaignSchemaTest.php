<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdvancedEmailCampaignSchemaTest extends TestCase
{
    public function test_advanced_email_campaign_schema_exists(): void
    {
        $this->assertTrue(Schema::hasColumns('email_campaigns', [
            'paused_at',
            'cancelled_at',
            'completed_at',
            'parent_campaign_id',
            'audience_filter_type',
            'audience_filter_value',
            'template_id',
            'last_batch_sent_at',
        ]));

        $this->assertTrue(Schema::hasColumns('email_campaign_recipients', [
            'suppressed_at',
            'suppression_reason',
            'first_clicked_at',
            'last_clicked_at',
            'click_count',
            'unsubscribed_at',
            'failure_count',
        ]));

        $this->assertTrue(Schema::hasTable('email_suppressions'));
        $this->assertTrue(Schema::hasTable('email_campaign_activity_logs'));
        $this->assertTrue(Schema::hasTable('email_campaign_templates'));
        $this->assertTrue(Schema::hasTable('email_campaign_clicks'));
    }
}
