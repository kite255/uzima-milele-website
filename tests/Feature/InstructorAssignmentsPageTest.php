<?php

namespace Tests\Feature;

use App\Filament\Pages\InstructorAssignments;
use Tests\TestCase;

class InstructorAssignmentsPageTest extends TestCase
{
    public function test_instructor_assignments_page_keeps_expected_row_structure(): void
    {
        $page = new InstructorAssignments();

        $this->assertTrue(
            property_exists($page, 'rows')
        );

        $this->assertTrue(
            method_exists($page, 'loadRows')
        );

        $this->assertTrue(
            method_exists($page, 'mount')
        );

        $this->assertSame(
            'filament.pages.instructor-assignments',
            (new \ReflectionClass($page))
                ->getStaticPropertyValue('view')
        );
    }
}