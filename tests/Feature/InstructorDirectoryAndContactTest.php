<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstructorDirectoryAndContactTest extends TestCase
{
    public function test_admin_instructor_assignments_page_exists(): void
    {
        $this->assertTrue(
            class_exists(
                \App\Filament\Pages\InstructorAssignments::class
            )
        );
    }

    public function test_instructor_assignments_view_exists(): void
    {
        $this->assertTrue(
            view()->exists(
                'filament.pages.instructor-assignments'
            )
        );
    }

    public function test_student_lesson_page_contains_assigned_instructor_contact_section(): void
    {
        $contents = file_get_contents(
            resource_path('views/lessons/show.blade.php')
        );

        $this->assertStringContainsString(
            'followUpInstructor',
            $contents
        );

        $this->assertStringContainsString(
            'Mwalimu wako',
            $contents
        );

        $this->assertStringContainsString(
            'WhatsApp',
            $contents
        );
    }

    public function test_public_ministry_identity_is_still_present(): void
    {
        $contents = file_get_contents(
            resource_path('views/lessons/show.blade.php')
        );

        $this->assertStringContainsString(
            'Uzima Milele Ministry',
            $contents
        );
    }
}