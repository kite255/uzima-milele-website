<?php

namespace Tests\Feature;

use Tests\TestCase;

class EmailSubscriberHygieneFilamentTest extends TestCase
{
    public function test_subscriber_form_uses_email_hygiene_service_for_helper_text(): void
    {
        $source = file_get_contents(
            app_path('Filament/Resources/EmailSubscriberResource.php')
        );

        $this->assertIsString($source);
        $this->assertStringContainsString('EmailAddressHygieneService', $source);
        $this->assertStringContainsString("TextInput::make('email')", $source);
        $this->assertStringContainsString('->live(onBlur: true)', $source);
        $this->assertStringContainsString('->helperText(', $source);
        $this->assertStringContainsString('suggestion(', $source);
        $this->assertStringNotContainsString("\$set('email'", $source);
    }
}
