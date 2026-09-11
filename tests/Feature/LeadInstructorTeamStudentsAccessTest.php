<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadInstructorTeamStudentsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_instructor_can_view_students_assigned_to_team_member(): void
    {
        [$lead, $followUp, $student, $lesson] = $this->createTeam();

        LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $followUp->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($lead)
            ->get(
                route('instructor.team.students', [
                    'lesson' => $lesson,
                    'instructor' => $followUp,
                ])
            )
            ->assertOk()
            ->assertSee($followUp->name)
            ->assertSee($student->name)
            ->assertSee('Assigned Students');
    }

    public function test_follow_up_instructor_cannot_view_another_team_members_students(): void
    {
        [$lead, $followUp, $student, $lesson] = $this->createTeam();

        $otherFollowUp = User::factory()->create([
            'role' => 'instructor',
        ]);

        $lesson->followUpInstructors()->attach(
            $otherFollowUp->id
        );

        LessonEnrollment::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'follow_up_instructor_id' => $otherFollowUp->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($followUp)
            ->get(
                route('instructor.team.students', [
                    'lesson' => $lesson,
                    'instructor' => $otherFollowUp,
                ])
            )
            ->assertForbidden();
    }

    public function test_lead_of_another_lesson_cannot_view_team_students(): void
    {
        [$lead, $followUp, $student, $lesson] = $this->createTeam();

        $otherLead = User::factory()->create([
            'role' => 'instructor',
        ]);

        $this->actingAs($otherLead)
            ->get(
                route('instructor.team.students', [
                    'lesson' => $lesson,
                    'instructor' => $followUp,
                ])
            )
            ->assertForbidden();
    }

    private function createTeam(): array
    {
        $lead = User::factory()->create([
            'role' => 'instructor',
        ]);

        $followUp = User::factory()->create([
            'name' => 'Follow-up Team Instructor',
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'name' => 'Assigned Team Student',
            'role' => 'student',
        ]);

        $lesson = Lesson::query()->create([
            'title' => 'Team Student Access Lesson',
            'slug' => 'team-student-access-lesson',
            'description' => 'Test lesson.',
            'content' => 'Test content.',
            'is_published' => true,
            'lead_instructor_id' => $lead->id,
        ]);

        $lesson->followUpInstructors()->attach(
            $followUp->id
        );

        return [
            $lead,
            $followUp,
            $student,
            $lesson,
        ];
    }
}
