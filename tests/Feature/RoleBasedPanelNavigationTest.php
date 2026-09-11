<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RoleBasedPanelNavigationTest extends TestCase
{
    public function test_admin_can_access_admin_center_but_not_instructor_hub(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $this->get('/admin/admin-center')
            ->assertOk();

        $this->get('/admin/instructor-hub')
            ->assertForbidden();
    }

    public function test_instructor_can_access_instructor_hub_but_not_admin_center(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->actingAs($instructor);

        $this->get('/admin/instructor-hub')
            ->assertOk();

        $this->get('/admin/admin-center')
            ->assertForbidden();
    }
}