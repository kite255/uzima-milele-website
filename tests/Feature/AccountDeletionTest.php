<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Run with:
 * DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=AccountDeletionTest
 *
 * Never run destructive account tests against a real student database.
 */
class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // RefreshDatabase can issue migrate:fresh. Require an explicitly
        // selected in-memory database before booting the Laravel test app.
        if (getenv('DB_CONNECTION') !== 'sqlite' || getenv('DB_DATABASE') !== ':memory:') {
            $this->markTestSkipped('Use DB_CONNECTION=sqlite DB_DATABASE=:memory: to run account deletion tests safely.');
        }

        parent::setUp();

        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_guest_cannot_delete_an_account(): void
    {
        $this->delete(route('profile.destroy'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_delete_account_with_wrong_password(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'incorrect-password'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrorsIn('userDeletion', ['password']);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_student_can_delete_account_with_correct_password(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_google_linked_student_can_reset_password_before_deletion(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'google_id' => 'test-google-id',
            'password' => Hash::make(Str::random(40)),
        ]);

        $token = Password::broker()->createToken($user);

        $this->post(route('password.store'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewSafePassword2026!',
            'password_confirmation' => 'NewSafePassword2026!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewSafePassword2026!', $user->fresh()->password));

        $this->actingAs($user->fresh())
            ->delete(route('profile.destroy'), ['password' => 'NewSafePassword2026!'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
