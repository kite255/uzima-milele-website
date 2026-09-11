<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class InstructorHubTest extends TestCase
{
    public function test_instructor_can_open_instructor_hub(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->actingAs($instructor)
            ->get('/admin/instructor-hub')
            ->assertOk()
            ->assertSee('Instructor Hub')
            ->assertSee('Assigned Students')
            ->assertSee('Due Follow-ups');
    }

    public function test_admin_cannot_open_instructor_hub(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin/instructor-hub')
            ->assertForbidden();
    }
}