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
            ->assertSee('Median')
            ->assertSee('Elfsight')
            ->assertSee('3. Faragha ya watoto')
            ->assertSee('id="faragha-ya-watoto"', false)
            ->assertSee('Uzima Milele Watoto')
            ->assertSee('haki-na-ufutaji')
            ->assertSee('7. Mawasiliano ya faragha')
            ->assertSee('info@uzimamilele.or.tz')
            ->assertSee('Utaratibu wa Kufuta Akaunti')
            ->assertDontSee('Rasimu inayosubiri uthibitisho wa mwisho')
            ->assertDontSee('idhini ya uongozi');
    }

    public function test_terms_page_is_public_and_links_to_privacy_and_deletion(): void
    {
        $this->get('/terms-and-conditions')
            ->assertOk()
            ->assertSee('Masharti na Vigezo vya Matumizi')
            ->assertSee('Uzima Milele Watoto')
            ->assertSee('Sera ya Faragha')
            ->assertSee('Utaratibu wa Kufuta Akaunti')
            ->assertSee('info@uzimamilele.or.tz');
    }

    public function test_account_deletion_information_is_public(): void
    {
        $this->get('/account-deletion')
            ->assertOk()
            ->assertSee('Kufuta Akaunti ya Uzima Milele')
            ->assertSee('info@uzimamilele.or.tz')
            ->assertSee('Umesahau nenosiri?')
            ->assertSee('Maombi ya mzazi au mlezi kuhusu mtoto')
            ->assertSee('Futa Akaunti');
    }
}
