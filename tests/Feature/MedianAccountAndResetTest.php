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
