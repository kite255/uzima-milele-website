<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstructorPublicIdentityAndModuleFilterTest extends TestCase
{
    public function test_module_resource_filter_no_longer_uses_direct_legacy_instructor_filter(): void
    {
        $contents = file_get_contents(
            app_path('Filament/Resources/ModuleResource.php')
        );

        $this->assertStringNotContainsString(
            "return \$query->where('instructor_id', auth()->id());",
            $contents
        );

        $this->assertStringContainsString(
            'static::applyInstructorLessonScope(',
            $contents
        );
    }

    public function test_public_lesson_page_does_not_expose_individual_instructor_identity(): void
    {
        $contents = file_get_contents(
            resource_path('views/lessons/show.blade.php')
        );

        $this->assertStringNotContainsString(
            '$instructorName',
            $contents
        );

        $this->assertStringNotContainsString(
            '$showInstructorName',
            $contents
        );

        $this->assertStringNotContainsString(
            '$lesson->instructor?->name',
            $contents
        );

        $this->assertStringContainsString(
            'Uzima Milele Ministry',
            $contents
        );
    }
}