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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $this->rows = Lesson::query()
            ->with([
                'leadInstructor',
                'followUpInstructors',
            ])
            ->orderBy('title')
            ->get()
            ->flatMap(function (Lesson $lesson): Collection {
                $rows = collect();

                if ($lesson->leadInstructor) {
                    $students = LessonEnrollment::query()
                        ->with('user')
                        ->where('lesson_id', $lesson->id)
                        ->get()
                        ->filter(fn (LessonEnrollment $enrollment) => $enrollment->user)
                        ->map(fn (LessonEnrollment $enrollment) => [
                            'id' => $enrollment->user->id,
                            'name' => $enrollment->user->name,
                            'email' => $enrollment->user->email,
                            'phone' => $enrollment->user->phone,
                        ])
                        ->values();

                    $rows->push([
                        'lesson_id' => $lesson->id,
                        'lesson_title' => $lesson->title,
                        'instructor_id' => $lesson->leadInstructor->id,
                        'instructor_name' => $lesson->leadInstructor->name,
                        'instructor_email' => $lesson->leadInstructor->email,
                        'instructor_phone' => $lesson->leadInstructor->phone,
                        'assignment_role' => 'Lead Instructor',
                        'student_count' => $students->count(),
                        'students' => $students,
                    ]);
                }

                foreach ($lesson->followUpInstructors as $instructor) {
                    $students = LessonEnrollment::query()
                        ->with('user')
                        ->where('lesson_id', $lesson->id)
                        ->where(
                            'follow_up_instructor_id',
                            $instructor->id
                        )
                        ->get()
                        ->filter(fn (LessonEnrollment $enrollment) => $enrollment->user)
                        ->map(fn (LessonEnrollment $enrollment) => [
                            'id' => $enrollment->user->id,
                            'name' => $enrollment->user->name,
                            'email' => $enrollment->user->email,
                            'phone' => $enrollment->user->phone,
                        ])
                        ->values();

                    $rows->push([
                        'lesson_id' => $lesson->id,
                        'lesson_title' => $lesson->title,
                        'instructor_id' => $instructor->id,
                        'instructor_name' => $instructor->name,
                        'instructor_email' => $instructor->email,
                        'instructor_phone' => $instructor->phone,
                        'assignment_role' => 'Follow-up Instructor',
                        'student_count' => $students->count(),
                        'students' => $students,
                    ]);
                }

                return $rows;
            })
            ->sortBy([
                ['lesson_title', 'asc'],
                ['assignment_role', 'asc'],
                ['instructor_name', 'asc'],
            ])
            ->values();
    }
}
