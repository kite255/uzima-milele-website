<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_privacy_policy_page_is_public(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Sera ya Faragha')
            ->assertSee('Google Play')
            ->assertSee('Median')
            ->assertSee('Elfsight')
            ->assertSee('Faragha ya watoto')
            ->assertSee('Uzima Milele Watoto')
            ->assertSee('youtube-nocookie.com')
            ->assertSee('info@uzimamilele.or.tz')
            ->assertSee('Utaratibu wa Kufuta Akaunti');
    }

    public function test_account_deletion_information_is_public(): void
    {
        $this->get('/account-deletion')
            ->assertOk()
            ->assertSee('Kufuta Akaunti ya Uzima Milele')
            ->assertSee('info@uzimamilele.or.tz')
            ->assertSee('Umesahau nenosiri?');
    }
}
