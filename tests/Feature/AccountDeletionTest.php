<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Run with:
 * DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=AccountDeletionTest
 *
 * Never run destructive account tests against a real student database.
 * This isolated schema cannot prove production data-cascade behavior.
 */
class AccountDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        // Require a disposable in-memory database. The project contains
        // MySQL-specific migrations which cannot run under SQLite.
        if (getenv('DB_CONNECTION') !== 'sqlite' || getenv('DB_DATABASE') !== ':memory:') {
            $this->markTestSkipped('Use DB_CONNECTION=sqlite DB_DATABASE=:memory: to run account deletion tests safely.');
        }

        parent::setUp();

        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        // Exercise authentication and deletion without running the project's
        // MySQL-only migration history. This is a controller-level test; it
        // does NOT verify full production foreign-key cascade behavior.
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('student');
            $table->string('google_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // The authenticated profile layout queries unread database notifications.
        // Model its dependency in this isolated in-memory controller test.
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_signed_in_student_can_open_profile_with_delete_account_control(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Taarifa Binafsi')
            ->assertSee('Usalama wa Akaunti')
            ->assertSee('Futa Akaunti')
            ->assertSee('Hifadhi Mabadiliko')
            ->assertSee('action="' . route('profile.destroy') . '"', false)
            ->assertSee('name="_method"', false);
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

        Password::broker()->createToken($user);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);

        DB::enableQueryLog();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect('/')
            ->assertSessionHasNoErrors();

        // Check the persisted record, not the original PHP model's stale
        // $exists property (the request may use another model instance).
        $stillExists = DB::table('users')->where('id', $user->id)->exists();
        $queries = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(fn (string $query): bool => str_contains(strtolower($query), 'users')
                || str_contains(strtolower($query), 'password_reset_tokens'))
            ->values()
            ->all();
        DB::disableQueryLog();
        DB::flushQueryLog();

        $this->assertFalse(
            $stillExists,
            'User row remains after DELETE /profile. Relevant SQL: ' . json_encode($queries)
        );

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
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
            ->assertRedirect('/')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
