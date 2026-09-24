<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailCampaignResource;
use App\Filament\Resources\EmailCampaignTemplateResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedEmailCampaignAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_campaign_and_template_resources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $this->assertTrue(EmailCampaignResource::canViewAny());
        $this->assertTrue(EmailCampaignTemplateResource::canViewAny());
    }

    public function test_instructor_cannot_access_campaign_or_template_management(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($instructor);

        $this->assertFalse(EmailCampaignResource::canViewAny());
        $this->assertFalse(EmailCampaignTemplateResource::canViewAny());
    }
}
