<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLoginBrandingTest extends TestCase
{
    public function test_admin_login_uses_uzima_milele_branding(): void
    {
        $response = $this->get('/admin/login');

        $response
            ->assertOk()
            ->assertSee('Karibu Tena')
            ->assertSee('Uzima Milele')
            ->assertSee('Ingia');
    }

    public function test_admin_panel_forces_light_mode(): void
    {
        $response = $this->get('/admin/login');

        $response
            ->assertOk()
            ->assertSee(
                "localStorage.setItem('theme', 'light')",
                false
            );
    }
}