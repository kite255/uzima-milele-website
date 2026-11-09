<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Isolated auth smoke tests for the Median webview launch.
 *
 * Run with:
 * DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=MobileAuthReadinessTest
 *
 * Do not run against an existing student database.
 */
class MobileAuthReadinessTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('DB_CONNECTION') !== 'sqlite' || getenv('DB_DATABASE') !== ':memory:') {
            $this->markTestSkipped('Requires an isolated SQLite in-memory database.');
        }

        parent::setUp();

        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('student');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_student_can_register_and_be_authenticated(): void
    {
        $this->post(route('register'), [
            'name' => 'Median Test Student',
            'email' => 'median-student@example.test',
            'password' => 'SecureTestPassword123!',
            'password_confirmation' => 'SecureTestPassword123!',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'median-student@example.test',
            'role' => 'student',
        ]);
    }

    public function test_student_can_log_in_and_out(): void
    {
        $student = User::factory()->create([
            'email' => 'login-test@example.test',
            'password' => Hash::make('SecureTestPassword123!'),
            'role' => 'student',
        ]);

        $this->post(route('login'), [
            'email' => $student->email,
            'password' => 'SecureTestPassword123!',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($student);

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
    }
}
