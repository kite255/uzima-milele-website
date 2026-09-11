<?php

namespace App\Filament\Pages;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class InstructorAssignments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?string $navigationLabel = 'Instructor Assignments';

    protected static ?string $title = 'Instructor Assignments';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.instructor-assignments';

    public Collection $rows;

    /**
     * Only administrators should see the page in Filament navigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    /**
     * Only administrators may access the page.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    /**
     * Load the assignment rows when the page opens.
     */
    public function mount(): void
    {
        $this->loadRows();
    }

    /**
     * Build the instructor-assignment table.
     *
     * Lead instructors:
     * - See every enrolled student for their lesson.
     *
     * Follow-up instructors:
     * - See only students specifically assigned to them.
     */
    public function loadRows(): void
    {
        $lessons = Lesson::query()
            ->with([
                'leadInstructor',
                'followUpInstructors',
            ])
            ->orderBy('title')
            ->get();

        $lessonIds = $lessons->pluck('id');

        $enrollmentsByLesson = LessonEnrollment::query()
            ->with('user')
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->filter(
                fn (LessonEnrollment $enrollment): bool =>
                    $enrollment->user !== null
            )
            ->groupBy('lesson_id');

        $this->rows = $lessons
            ->flatMap(
                fn (Lesson $lesson): Collection =>
                    $this->buildLessonRows(
                        $lesson,
                        $enrollmentsByLesson->get(
                            $lesson->id,
                            collect()
                        )
                    )
            )
            ->sortBy([
                ['lesson_title', 'asc'],
                ['assignment_role', 'asc'],
                ['instructor_name', 'asc'],
            ])
            ->values();
    }

    /**
     * Build all instructor rows for one lesson.
     */
    protected function buildLessonRows(
        Lesson $lesson,
        Collection $enrollments
    ): Collection {
        $rows = collect();

        if ($lesson->leadInstructor) {
            $rows->push(
                $this->makeAssignmentRow(
                    lesson: $lesson,
                    instructor: $lesson->leadInstructor,
                    role: 'Lead Instructor',
                    students: $this->mapStudents($enrollments)
                )
            );
        }

        foreach ($lesson->followUpInstructors as $instructor) {
            $assignedEnrollments = $enrollments->filter(
                fn (LessonEnrollment $enrollment): bool =>
                    (int) $enrollment->follow_up_instructor_id
                    === (int) $instructor->id
            );

            $rows->push(
                $this->makeAssignmentRow(
                    lesson: $lesson,
                    instructor: $instructor,
                    role: 'Follow-up Instructor',
                    students: $this->mapStudents(
                        $assignedEnrollments
                    )
                )
            );
        }

        return $rows;
    }

    /**
     * Convert lesson enrollments into the student structure used by the Blade.
     */
    protected function mapStudents(
        Collection $enrollments
    ): Collection {
        return $enrollments
            ->filter(
                fn (LessonEnrollment $enrollment): bool =>
                    $enrollment->user !== null
            )
            ->map(
                fn (LessonEnrollment $enrollment): array => [
                    'id' => $enrollment->user->id,
                    'name' => $enrollment->user->name,
                    'email' => $enrollment->user->email,
                    'phone' => $enrollment->user->phone,
                ]
            )
            ->values();
    }

    /**
     * Create the row structure expected by the existing Blade view.
     */
    protected function makeAssignmentRow(
        Lesson $lesson,
        $instructor,
        string $role,
        Collection $students
    ): array {
        return [
            'lesson_id' => $lesson->id,
            'lesson_title' => $lesson->title,

            'instructor_id' => $instructor->id,
            'instructor_name' => $instructor->name,
            'instructor_email' => $instructor->email,
            'instructor_phone' => $instructor->phone,

            'assignment_role' => $role,

            'student_count' => $students->count(),
            'students' => $students,
        ];
    }
}