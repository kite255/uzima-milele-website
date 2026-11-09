<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicLegalPagesReleaseTest extends TestCase
{
    public function test_privacy_policy_is_public_and_has_children_section(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Sera ya Faragha')
            ->assertSee('Uzima Milele Watoto')
            ->assertSee('id="faragha-ya-watoto"', false)
            ->assertSee('info@uzimamilele.or.tz')
            ->assertDontSee('Rasimu inayosubiri uthibitisho wa mwisho')
            ->assertDontSee('Rasimu inayopitiwa');
    }

    public function test_terms_page_is_public(): void
    {
        $this->get('/terms-and-conditions')
            ->assertOk()
            ->assertSee('Masharti na Vigezo vya Matumizi')
            ->assertSee('Uzima Milele Watoto')
            ->assertSee('Sera ya Faragha')
            ->assertDontSee('Rasimu inayohitaji idhini');
    }

    public function test_account_deletion_page_is_public(): void
    {
        $this->get('/account-deletion')
            ->assertOk()
            ->assertSee('Kufuta Akaunti ya Uzima Milele')
            ->assertSee('Maombi ya mzazi au mlezi kuhusu mtoto')
            ->assertSee('info@uzimamilele.or.tz');
    }
}
