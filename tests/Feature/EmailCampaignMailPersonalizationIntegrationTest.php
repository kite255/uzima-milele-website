<?php

namespace Tests\Feature;

use App\Mail\CustomCampaignMail;
use App\Mail\DevotionCampaignMail;
use App\Models\Devotion;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignMailPersonalizationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_campaign_mail_personalizes_subject_for_recipient(): void
    {
        [$subscriber, $recipient] = $this->subscriberAndRecipient();

        $campaign = EmailCampaign::query()->create([
            'name' => 'Personalized Custom',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Habari {{first_name}} - {{campaign_name}}',
            'content' => '<p>Hello {{name}}</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $recipient->update(['email_campaign_id' => $campaign->id]);
        $recipient->refresh();

        $mail = new CustomCampaignMail($campaign, $recipient);
        $mail->build();

        $this->assertSame(
            'Habari Neema - Personalized Custom',
            $mail->subject
        );
    }

    public function test_devotion_campaign_mail_personalizes_subject_with_devotion_values(): void
    {
        $devotion = Devotion::query()->create([
            'title' => 'Mtema-miti — Bidii Inayoleta Matokeo',
            'slug' => 'mtema-miti-bidii-inayoleta-matokeo',
            'content' => '<p>Devotion content</p>',
            'published_at' => now()->toDateString(),
        ]);

        [$subscriber, $recipient] = $this->subscriberAndRecipient();

        $campaign = EmailCampaign::query()->create([
            'name' => 'Morning Devotion',
            'type' => EmailCampaign::TYPE_DEVOTION,
            'devotion_id' => $devotion->id,
            'subject' => '{{devotion_title}} - {{first_name}}',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $recipient->update(['email_campaign_id' => $campaign->id]);
        $recipient->refresh();

        $mail = new DevotionCampaignMail($campaign->fresh('devotion'), $recipient);
        $mail->build();

        $this->assertSame(
            'Mtema-miti — Bidii Inayoleta Matokeo - Neema',
            $mail->subject
        );
    }

    private function subscriberAndRecipient(): array
    {
        $subscriber = EmailSubscriber::query()->create([
            'first_name' => 'Neema',
            'last_name' => 'Mushi',
            'name' => 'Neema Mushi',
            'email' => 'neema@example.com',
            'status' => 'subscribed',
            'language' => 'sw',
        ]);

        $placeholderCampaign = EmailCampaign::query()->create([
            'name' => 'Placeholder',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Placeholder',
            'content' => '<p>Placeholder</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $recipient = EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $placeholderCampaign->id,
            'email_subscriber_id' => $subscriber->id,
            'name' => $subscriber->name,
            'email' => $subscriber->email,
            'status' => EmailCampaignRecipient::STATUS_PENDING,
        ]);

        return [$subscriber, $recipient];
    }
}
