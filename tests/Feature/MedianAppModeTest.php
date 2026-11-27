<?php

namespace Tests\Feature;

use Tests\TestCase;

class MedianAppModeTest extends TestCase
{
    public function test_regular_browser_has_website_navigation(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Urambazaji wa programu');
    }

    public function test_google_sign_in_is_hidden_in_median_mode_but_visible_on_website(): void
    {
        $this->get('/?app=1')->assertOk();

        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Endelea kwa kutumia Google')
            ->assertSee('Umesahau nenosiri?');

        $this->get('/?app=0')->assertOk();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Endelea kwa kutumia Google');
    }

    public function test_native_google_button_requires_explicit_configuration(): void
    {
        config(['services.google.median_client_id' => 'test-client.apps.googleusercontent.com']);
        $this->get('/?app=1')->assertOk();
        $this->get('/login')->assertOk()->assertDontSee('median-google-button');
        config(['services.google.median_login_enabled' => true]);
        $this->get('/?app=1')->assertOk();
        $this->get('/login')->assertOk()->assertSee('median-google-button');
        $this->postJson('/auth/median/google', [])->assertUnprocessable();
        $this->get('/?app=0')->assertOk();
        $this->postJson('/auth/median/google', ['id_token' => 'invalid'])->assertForbidden();
    }

    public function test_median_launch_enables_app_navigation_across_pages(): void
    {
        $this->get('/?app=1')
            ->assertOk()
            ->assertSee('Urambazaji wa programu');

        $this->get('/lessons')
            ->assertOk()
            ->assertSee('Urambazaji wa programu');

        $this->get('/?app=0')
            ->assertOk()
            ->assertDontSee('Urambazaji wa programu');
    }
}
