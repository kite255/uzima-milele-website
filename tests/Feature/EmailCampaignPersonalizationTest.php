<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Services\Email\CampaignPersonalizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignPersonalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_placeholders_render_with_safe_values_and_no_raw_supported_tokens(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'first_name' => 'Neema',
            'last_name' => 'Mushi',
            'name' => 'Neema Mushi',
            'email' => 'neema@example.com',
            'status' => 'subscribed',
            'language' => 'sw',
        ]);

        $campaign = EmailCampaign::query()->create([
            'name' => 'Morning Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Ujumbe wa Leo',
            'content' => 'Test',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $recipient = EmailCampaignRecipient::query()->create([
            'email_campaign_id' => $campaign->id,
            'email_subscriber_id' => $subscriber->id,
            'name' => $subscriber->name,
            'email' => $subscriber->email,
            'status' => EmailCampaignRecipient::STATUS_PENDING,
        ]);

        $content = implode('|', [
            '{{first_name}}',
            '{{last_name}}',
            '{{name}}',
            '{{email}}',
            '{{language}}',
            '{{campaign_name}}',
            '{{campaign_subject}}',
            '{{devotion_title}}',
            '{{devotion_url}}',
            '{{unsubscribe_url}}',
        ]);

        $rendered = app(CampaignPersonalizationService::class)->render(
            $content,
            $campaign,
            $recipient,
            $subscriber
        );

        $this->assertStringContainsString('Neema', $rendered);
        $this->assertStringContainsString('Mushi', $rendered);
        $this->assertStringContainsString('Neema Mushi', $rendered);
        $this->assertStringContainsString('neema@example.com', $rendered);
        $this->assertStringContainsString('sw', $rendered);
        $this->assertStringContainsString('Morning Campaign', $rendered);
        $this->assertStringContainsString('Ujumbe wa Leo', $rendered);
        $this->assertStringContainsString(
            route('email-subscribers.unsubscribe', $subscriber->unsubscribe_token),
            $rendered
        );

        foreach ([
            'first_name', 'last_name', 'name', 'email', 'language',
            'campaign_name', 'campaign_subject', 'devotion_title',
            'devotion_url', 'unsubscribe_url',
        ] as $placeholder) {
            $this->assertStringNotContainsString('{{'.$placeholder.'}}', $rendered);
        }
    }

    public function test_missing_values_use_safe_fallbacks_and_unknown_placeholders_are_not_executed(): void
    {
        $subscriber = EmailSubscriber::query()->create([
            'name' => '',
            'email' => 'anonymous@example.com',
            'status' => 'subscribed',
            'language' => 'sw',
        ]);

        $campaign = EmailCampaign::query()->create([
            'name' => 'Fallback Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Fallback Test',
            'content' => 'Test',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $content = 'Habari {{first_name}} | {{devotion_title}} | {{unknown_key}} | {{ $dangerous }}';

        $rendered = app(CampaignPersonalizationService::class)->render(
            $content,
            $campaign,
            null,
            $subscriber
        );

        $this->assertStringNotContainsString('{{first_name}}', $rendered);
        $this->assertStringNotContainsString('{{devotion_title}}', $rendered);
        $this->assertStringContainsString('{{unknown_key}}', $rendered);
        $this->assertStringContainsString('{{ $dangerous }}', $rendered);
    }

    public function test_variables_returns_only_the_supported_placeholder_keys(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Variable Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Variables',
            'content' => 'Test',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $variables = app(CampaignPersonalizationService::class)->variables(
            $campaign
        );

        $this->assertSame([
            'first_name',
            'last_name',
            'name',
            'email',
            'language',
            'campaign_name',
            'campaign_subject',
            'devotion_title',
            'devotion_url',
            'unsubscribe_url',
        ], array_keys($variables));
    }
}
