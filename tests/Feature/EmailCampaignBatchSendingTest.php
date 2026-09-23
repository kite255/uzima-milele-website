<?php

namespace Tests\Feature;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCampaignBatchSendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_sends_only_configured_batch_and_queues_next_batch(): void
    {
        config()->set('mail.campaign_batch_size', 30);
        config()->set('mail.campaign_batch_delay_minutes', 5);

        Mail::fake();
        Queue::fake();

        $campaign = EmailCampaign::query()->create([
            'name' => 'Batch Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Batch Test',
            'content' => '<p>Hello</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_QUEUED,
            'total_recipients' => 35,
            'sent_count' => 0,
            'failed_count' => 0,
            'queued_at' => now(),
        ]);

        foreach (range(1, 35) as $index) {
            EmailCampaignRecipient::query()->create([
                'email_campaign_id' => $campaign->id,
                'name' => "Recipient {$index}",
                'email' => "recipient{$index}@example.com",
                'status' => EmailCampaignRecipient::STATUS_PENDING,
            ]);
        }

        (new SendEmailCampaign($campaign->id))->handle();

        $campaign->refresh();

        $this->assertSame(30, $campaign->sent_count);
        $this->assertSame(0, $campaign->failed_count);
        $this->assertSame(EmailCampaign::STATUS_SENDING, $campaign->status);

        $this->assertSame(
            5,
            EmailCampaignRecipient::query()
                ->where('email_campaign_id', $campaign->id)
                ->where('status', EmailCampaignRecipient::STATUS_PENDING)
                ->count()
        );

        Queue::assertPushed(
            SendEmailCampaign::class,
            fn (SendEmailCampaign $job): bool =>
                $job->campaignId === $campaign->id
                && $job->delay !== null
        );
    }

    public function test_final_batch_completes_campaign_without_queuing_another_batch(): void
    {
        config()->set('mail.campaign_batch_size', 30);
        config()->set('mail.campaign_batch_delay_minutes', 5);

        Mail::fake();
        Queue::fake();

        $campaign = EmailCampaign::query()->create([
            'name' => 'Final Batch Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Final Batch Test',
            'content' => '<p>Hello</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_SENDING,
            'total_recipients' => 5,
            'sent_count' => 0,
            'failed_count' => 0,
            'queued_at' => now(),
        ]);

        foreach (range(1, 5) as $index) {
            EmailCampaignRecipient::query()->create([
                'email_campaign_id' => $campaign->id,
                'name' => "Final Recipient {$index}",
                'email' => "final{$index}@example.com",
                'status' => EmailCampaignRecipient::STATUS_PENDING,
            ]);
        }

        (new SendEmailCampaign($campaign->id))->handle();

        $campaign->refresh();

        $this->assertSame(5, $campaign->sent_count);
        $this->assertSame(0, $campaign->failed_count);
        $this->assertSame(EmailCampaign::STATUS_SENT, $campaign->status);
        $this->assertNotNull($campaign->sent_at);

        Queue::assertNotPushed(SendEmailCampaign::class);
    }
}
