<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignTemplate;
use App\Services\Email\CampaignTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class EmailCampaignTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_service_applies_subject_content_type_and_template_reference(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Campaign Before Template',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Old Subject',
            'content' => '<p>Old content</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $template = EmailCampaignTemplate::query()->create([
            'name' => 'Newsletter Standard',
            'slug' => 'newsletter-standard',
            'subject' => 'Habari {{first_name}}',
            'content' => '<p>Karibu kwenye newsletter yetu.</p>',
            'type' => 'newsletter',
            'is_active' => true,
        ]);

        app(CampaignTemplateService::class)->apply($campaign, $template);

        $campaign->refresh();

        $this->assertSame($template->id, $campaign->template_id);
        $this->assertSame('Habari {{first_name}}', $campaign->subject);
        $this->assertSame('<p>Karibu kwenye newsletter yetu.</p>', $campaign->content);
        $this->assertSame(EmailCampaign::TYPE_CUSTOM, $campaign->type);
    }

    public function test_template_types_are_limited_to_approved_values(): void
    {
        $service = app(CampaignTemplateService::class);

        $this->assertSame([
            'devotion',
            'children_devotion',
            'newsletter',
            'announcement',
            'lesson_reminder',
            'special_event',
            'general',
        ], $service->allowedTypes());

        $template = EmailCampaignTemplate::query()->create([
            'name' => 'Invalid Template',
            'slug' => 'invalid-template',
            'subject' => 'Invalid',
            'content' => '<p>Invalid</p>',
            'type' => 'unsupported_type',
            'is_active' => true,
        ]);

        $campaign = EmailCampaign::query()->create([
            'name' => 'Template Validation',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Original',
            'content' => '<p>Original</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $service->apply($campaign, $template);
    }

    public function test_inactive_template_cannot_be_applied(): void
    {
        $campaign = EmailCampaign::query()->create([
            'name' => 'Inactive Template Campaign',
            'type' => EmailCampaign::TYPE_CUSTOM,
            'subject' => 'Original',
            'content' => '<p>Original</p>',
            'recipient_scope' => EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
            'status' => EmailCampaign::STATUS_DRAFT,
        ]);

        $template = EmailCampaignTemplate::query()->create([
            'name' => 'Inactive Template',
            'slug' => 'inactive-template',
            'subject' => 'Do not use',
            'content' => '<p>Inactive</p>',
            'type' => 'general',
            'is_active' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(CampaignTemplateService::class)->apply($campaign, $template);
    }
}
