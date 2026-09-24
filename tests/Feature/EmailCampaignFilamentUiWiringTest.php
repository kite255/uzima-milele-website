<?php

namespace Tests\Feature;

use Tests\TestCase;

class EmailCampaignFilamentUiWiringTest extends TestCase
{
    public function test_campaign_resource_wires_advanced_lifecycle_actions_to_services(): void
    {
        $source = file_get_contents(
            app_path('Filament/Resources/EmailCampaignResource.php')
        );

        $this->assertIsString($source);

        foreach ([
            "Action::make('pause')",
            "Action::make('resume')",
            "Action::make('cancel')",
            "Action::make('duplicate')",
            "Action::make('resend_non_openers')",
            "Action::make('retry_failed')",
            "Action::make('export')",
        ] as $action) {
            $this->assertStringContainsString($action, $source);
        }

        $this->assertStringContainsString('CampaignSendingService', $source);
        $this->assertStringContainsString('CampaignCloneService', $source);
        $this->assertStringContainsString('CampaignExportService', $source);
        $this->assertStringContainsString('availableActionsFor($record)', $source);
    }
}
