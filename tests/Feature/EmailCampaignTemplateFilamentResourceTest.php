<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailCampaignTemplateResource;
use App\Models\EmailCampaignTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignTemplateFilamentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_resource_uses_campaign_template_model_and_expected_pages(): void
    {
        $this->assertSame(
            EmailCampaignTemplate::class,
            EmailCampaignTemplateResource::getModel()
        );

        $pages = EmailCampaignTemplateResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_template_resource_is_grouped_under_email_navigation(): void
    {
        $this->assertSame(
            'Barua Pepe',
            EmailCampaignTemplateResource::getNavigationGroup()
        );
    }
}
