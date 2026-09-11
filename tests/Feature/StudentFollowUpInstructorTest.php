<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentFollowUpInstructorTest extends TestCase
{
    public function test_student_dashboard_contains_follow_up_instructor_section(): void
    {
        $view = file_get_contents(
            resource_path('views/student/dashboard.blade.php')
        );

        $this->assertStringContainsString(
            'Mwalimu wa Ufuatiliaji',
            $view
        );

        $this->assertStringContainsString(
            'follow_up_instructor_id',
            $view
        );

        $this->assertStringContainsString(
            '$followUpInstructor->name',
            $view
        );

        $this->assertStringContainsString(
            '$followUpInstructor->email',
            $view
        );

        $this->assertStringContainsString(
            '$followUpInstructor->phone',
            $view
        );
    }

    public function test_student_dashboard_has_unassigned_instructor_message(): void
    {
        $view = file_get_contents(
            resource_path('views/student/dashboard.blade.php')
        );

        $this->assertStringContainsString(
            'Mwalimu wa ufuatiliaji bado hajapangwa.',
            $view
        );

        $this->assertStringContainsString(
            'Utapata taarifa za mwalimu hapa baada ya kupangiwa.',
            $view
        );
    }
}