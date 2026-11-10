<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class GoogleAccountPasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_user_can_request_password_setup_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'google_id' => 'google-account-123',
        ]);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_google_user_can_set_password_without_losing_account_link(): void
    {
        $user = User::factory()->create([
            'google_id' => 'google-account-123',
        ]);
        $originalId = $user->id;
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertSame($originalId, $user->id);
        $this->assertSame('google-account-123', $user->google_id);
        $this->assertTrue(Hash::check('NewSecurePass123!', $user->password));
        $this->assertSame(1, User::where('email', $user->email)->count());

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'NewSecurePass123!',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }
}
