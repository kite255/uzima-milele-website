<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstructorAssignmentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_assignment_schema_exists(): void
    {
        $this->assertTrue(
            Schema::hasColumns('lessons', [
                'lead_instructor_id',
                'lead_can_receive_students',
            ])
        );

        $this->assertTrue(
            Schema::hasTable('lesson_follow_up_instructor')
        );

        $this->assertTrue(
            Schema::hasColumns('lesson_follow_up_instructor', [
                'lesson_id',
                'instructor_id',
            ])
        );

        $this->assertTrue(
            Schema::hasColumns('lesson_enrollments', [
                'follow_up_instructor_id',
                'instructor_assigned_at',
            ])
        );
    }
}