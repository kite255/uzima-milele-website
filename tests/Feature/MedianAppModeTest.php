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
