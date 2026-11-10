<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class MedianAccountAndResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_account_hub_contains_login_and_registration(): void
    {
        $this->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('Ingia kwenye akaunti')
            ->assertSee('Jisajili');
    }

    public function test_signed_in_account_hub_contains_profile_and_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('Wasifu wangu')
            ->assertSee('Toka kwenye akaunti');
    }

    public function test_privacy_policy_link_is_visible_for_guests_and_members(): void
    {
        $this->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('Sera ya Faragha')
            ->assertSee('href="'.route('privacy-policy').'"', false);

        $this->actingAs(User::factory()->create())
            ->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('href="'.route('privacy-policy').'"', false);

        $this->get(route('privacy-policy'))->assertOk();
    }

    public function test_terms_link_is_visible_in_median_account_for_guests_and_members(): void
    {
        $this->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('Masharti na Vigezo')
            ->assertSee('href="'.route('terms-and-conditions').'"', false);

        $this->actingAs(User::factory()->create())
            ->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('Masharti na Vigezo');

        $this->get(route('terms-and-conditions'))->assertOk();
    }

    public function test_account_deletion_information_is_accessible_to_guests_and_signed_in_users(): void
    {
        $this->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('Kufuta Akaunti')
            ->assertSee('href="'.route('account-deletion').'"', false);

        $this->actingAs(User::factory()->create())
            ->withSession(['median_app_mode' => true])
            ->get(route('app.account'))
            ->assertOk()
            ->assertSee('Kufuta Akaunti');

        $this->get(route('account-deletion'))->assertOk();
    }

    public function test_password_reset_link_shows_new_password_form_and_preserves_google_account(): void
    {
        $user = User::factory()->create(['google_id' => 'google-test-id']);
        $id = $user->id;
        $token = Password::createToken($user);

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]))
            ->assertOk()
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('name="token"', false)
            ->assertSee('action="'.route('password.store').'"', false);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'StrongNewPassword123!',
            'password_confirmation' => 'StrongNewPassword123!',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertSame($id, $user->id);
        $this->assertSame('google-test-id', $user->google_id);
        $this->assertTrue(Hash::check('StrongNewPassword123!', $user->password));
    }
}
