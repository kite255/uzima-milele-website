<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadInstructorTeamSupervisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_instructor_can_see_follow_up_team_and_student_counts(): void
    {
        $lead = User::factory()->create([
            'name' => 'Lead Instructor',
            'role' => 'instructor',
        ]);

        $followUpOne = User::factory()->create([
            'name' => 'Instructor Alpha',
            'role' => 'instructor',
        ]);

        $followUpTwo = User::factory()->create([
            'name' => 'Instructor Beta',
            'role' => 'instructor',
        ]);

        $studentOne = User::factory()->create([
            'name' => 'Student One',
            'role' => 'student',
        ]);

        $studentTwo = User::factory()->create([
            'name' => 'Student Two',
            'role' => 'student',
        ]);

        $studentThree = User::factory()->create([
            'name' => 'Student Three',
            'role' => 'student',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Team Supervision Lesson',
            'slug' => 'team-supervision-lesson',
            'description' => 'Test lesson.',
            'content' => 'Test content.',
            'is_published' => true,
            'lead_instructor_id' => $lead->id,
        ]);

        $lesson->followUpInstructors()->attach([
            $followUpOne->id,
            $followUpTwo->id,
        ]);

        LessonEnrollment::query()->create([
            'user_id' => $studentOne->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUpOne->id,
            'enrolled_at' => now(),
        ]);

        LessonEnrollment::query()->create([
            'user_id' => $studentTwo->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUpOne->id,
            'enrolled_at' => now(),
        ]);

        LessonEnrollment::query()->create([
            'user_id' => $studentThree->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUpTwo->id,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($lead)
            ->get(route('instructor.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Team Supervision')
            ->assertSee('Instructor Alpha')
            ->assertSee('Instructor Beta')
            ->assertViewHas(
                'teamSupervision',
                function ($teamSupervision) use (
                    $followUpOne,
                    $followUpTwo
                ) {
                    $alpha = $teamSupervision->first(
                        fn ($item) =>
                            (int) $item['instructor']->id === (int) $followUpOne->id
                    );

                    $beta = $teamSupervision->first(
                        fn ($item) =>
                            (int) $item['instructor']->id === (int) $followUpTwo->id
                    );

                    return $alpha !== null
                        && $beta !== null
                        && $alpha['student_count'] === 2
                        && $beta['student_count'] === 1;
                }
            );
    }

    public function test_lead_instructor_sees_unassigned_students(): void
    {
        $lead = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'name' => 'Unassigned Supervision Student',
            'role' => 'student',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Unassigned Team Lesson',
            'slug' => 'unassigned-team-lesson',
            'description' => 'Test lesson.',
            'content' => 'Test content.',
            'is_published' => true,
            'lead_instructor_id' => $lead->id,
        ]);

        $enrollment = LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => null,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($lead)
            ->get(route('instructor.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Unassigned Students')
            ->assertSee('Unassigned Supervision Student')
            ->assertViewHas(
                'unassignedStudents',
                fn ($unassignedStudents) =>
                    $unassignedStudents->contains(
                        'id',
                        $enrollment->id
                    )
            );
    }

    public function test_follow_up_instructor_does_not_see_team_supervision_section(): void
    {
        $lead = User::factory()->create([
            'role' => 'instructor',
        ]);

        $followUp = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Private Team Lesson',
            'slug' => 'private-team-lesson',
            'description' => 'Test lesson.',
            'content' => 'Test content.',
            'is_published' => true,
            'lead_instructor_id' => $lead->id,
        ]);

        $lesson->followUpInstructors()->attach(
            $followUp->id
        );

        LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUp->id,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($followUp)
            ->get(route('instructor.dashboard'));

        $response
            ->assertOk()
            ->assertDontSee('Team Supervision')
            ->assertViewHas(
                'canViewTeamSupervision',
                false
            );
    }
}