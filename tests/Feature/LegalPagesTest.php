<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_privacy_policy_page_is_public(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Sera ya Faragha');
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
