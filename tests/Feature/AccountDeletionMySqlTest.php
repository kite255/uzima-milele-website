<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Requires an EXISTING, separately provisioned and migrated MySQL test database.
 * Does not run migrate:fresh or modify production schema.
 *
 * Example:
 * APP_ENV=testing DB_CONNECTION=mysql DB_HOST=127.0.0.1 \
 * DB_DATABASE=uzima_milele_test UZIMA_ALLOW_MYSQL_TESTS=1 \
 * php artisan test --filter=AccountDeletionMySqlTest
 */
class AccountDeletionMySqlTest extends TestCase
{
    protected function setUp(): void
    {
        // Check the process environment before Laravel has a chance to connect.
        // Never permit an unqualified database name, even for skipped tests.
        $database = (string) getenv('DB_DATABASE');
        if (
            getenv('UZIMA_ALLOW_MYSQL_TESTS') !== '1'
            || getenv('DB_CONNECTION') !== 'mysql'
            || ! preg_match('/^(?:[a-z0-9_]+_test|[a-z0-9_]+_testing)$/i', $database)
        ) {
            $this->markTestSkipped(
                'Requires explicit opt-in and an isolated MySQL database ending _test or _testing.'
            );
        }

        parent::setUp();

        $connection = DB::connection();
        $configuredDatabase = (string) $connection->getDatabaseName();
        $host = (string) config('database.connections.mysql.host');

        // Reject any mismatch or remote host to avoid touching cPanel databases.
        if (
            config('database.default') !== 'mysql'
            || $configuredDatabase !== $database
            || ! in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || app()->environment() !== 'testing'
        ) {
            throw new \RuntimeException('MySQL integration tests must use a local, dedicated testing database.');
        }
    }

    public function test_student_foreign_key_deletion_rules_match_expected_retention(): void
    {
        // An audit of the schema actually installed in the local MySQL test DB.
        // CASCADE removes associated student data; SET NULL retains an anonymized row.
        $expected = [
            'lesson_enrollments' => ['user_id' => 'CASCADE'],
            'lesson_progress' => ['user_id' => 'CASCADE'],
            'quiz_attempts' => ['user_id' => 'CASCADE'],
            'certificates' => ['user_id' => 'CASCADE'],
            'user_topic_views' => ['user_id' => 'CASCADE'],
            'quiz_results' => ['user_id' => 'SET NULL'],
            'student_follow_ups' => ['lesson_enrollment_id' => 'CASCADE'],
        ];

        foreach ($expected as $table => $columns) {
            foreach ($columns as $column => $rule) {
                $actual = DB::table('information_schema.KEY_COLUMN_USAGE as k')
                    ->join('information_schema.REFERENTIAL_CONSTRAINTS as r', function ($join): void {
                        $join->on('k.CONSTRAINT_SCHEMA', '=', 'r.CONSTRAINT_SCHEMA')
                            ->on('k.CONSTRAINT_NAME', '=', 'r.CONSTRAINT_NAME')
                            ->on('k.TABLE_NAME', '=', 'r.TABLE_NAME');
                    })
                    ->where('k.TABLE_SCHEMA', DB::connection()->getDatabaseName())
                    ->where('k.TABLE_NAME', $table)
                    ->where('k.COLUMN_NAME', $column)
                    ->value('r.DELETE_RULE');

                $this->assertSame($rule, $actual, "Unexpected delete rule: {$table}.{$column}");
            }
        }
    }

    public function test_student_deletion_is_rolled_back_after_request(): void
    {
        // Writes are isolated to a local test DB and rolled back even when an assertion fails.
        DB::beginTransaction();

        try {
            $student = User::factory()->create(['role' => 'student']);
            $id = $student->id;

            $this->actingAs($student)
                ->delete(route('profile.destroy'), ['password' => 'password'])
                ->assertRedirect('/');

            $this->assertGuest();
            $this->assertDatabaseMissing('users', ['id' => $id]);
        } finally {
            DB::rollBack();
        }
    }
}
