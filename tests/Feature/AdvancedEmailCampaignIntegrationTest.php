<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdvancedEmailCampaignIntegrationTest extends TestCase
{
    public function test_public_email_routes_exist_without_auth_middleware(): void
    {
        foreach ([
            'email-subscribers.unsubscribe',
            'email-subscribers.preferences',
            'email-campaigns.open',
            'email-campaigns.click',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route {$name}");
            $this->assertNotContains('auth', $route->gatherMiddleware());
        }
    }

    public function test_click_tracking_route_is_declared_in_web_routes_not_service_provider(): void
    {
        $webRoutes = file_get_contents(base_path('routes/web.php'));
        $provider = file_get_contents(app_path('Providers/AppServiceProvider.php'));

        $this->assertIsString($webRoutes);
        $this->assertIsString($provider);
        $this->assertStringContainsString("/email/click/{token}", $webRoutes);
        $this->assertStringContainsString("email-campaigns.click", $webRoutes);
        $this->assertStringNotContainsString("/email/click/{token}", $provider);
    }

    public function test_rollout_configuration_is_documented_without_delivery_overclaim(): void
    {
        $env = file_get_contents(base_path('.env.example'));
        $readme = file_get_contents(base_path('README.md'));

        $this->assertIsString($env);
        $this->assertIsString($readme);

        $this->assertStringContainsString('QUEUE_CONNECTION=database', $env);
        $this->assertStringContainsString('MAIL_CAMPAIGN_BATCH_SIZE=20', $env);
        $this->assertStringContainsString('MAIL_CAMPAIGN_BATCH_DELAY_MINUTES=10', $env);

        $this->assertStringContainsString('MAIL_CAMPAIGN_BATCH_SIZE=20', $readme);
        $this->assertStringContainsString('MAIL_CAMPAIGN_BATCH_DELAY_MINUTES=10', $readme);
        $this->assertStringContainsString('Sent', $readme);
        $this->assertStringContainsString('cPanel SMTP', $readme);
    }
}
