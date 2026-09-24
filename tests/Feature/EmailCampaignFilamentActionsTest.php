<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignFilamentActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_actions_match_lifecycle_state(): void
    {
        $this->assertSame(
            ['preview', 'send_test', 'schedule', 'send_now', 'duplicate'],
            EmailCampaignResource::availableActionsFor($this->campaign(EmailCampaign::STATUS_DRAFT))
        );

        $this->assertSame(
            ['preview', 'cancel_schedule', 'duplicate'],
            EmailCampaignResource::availableActionsFor($this->campaign(EmailCampaign::STATUS_SCHEDULED))
        );

        $this->assertSame(
            ['pause', 'cancel'],
            EmailCampaignResource::availableActionsFor($this->campaign(EmailCampaign::STATUS_SENDING))
        );

        $this->assertSame(
            ['resume', 'cancel'],
            EmailCampaignResource::availableActionsFor($this->campaign(EmailCampaign::STATUS_PAUSED))
        );

        $this->assertSame(
            ['resend_non_openers', 'retry_failed', 'duplicate', 'export'],
            EmailCampaignResource::availableActionsFor($this->campaign(EmailCampaign::STATUS_COMPLETED))
        );

        $this->assertSame(
            ['resend_non_openers', 'retry_failed', 'duplicate', 'export'],
            EmailCampaignResource::availableActionsFor($this->campaign(EmailCampaign::STATUS_SENT))
        );

        $this->assertSame(
            ['retry_failed', 'duplicate'],
            EmailCampaignResource::availableActionsFor($this->campaign(EmailCampaign::STATUS_FAILED))
        );
    }

    private function campaign(string $status): EmailCampaign
    {
        return EmailCampaign::query()->create([
            'name' => 'Campaign '.$status,
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Subject',
            'content' => '<p>Content</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => $status,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);
    }
}
