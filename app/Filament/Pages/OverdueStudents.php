<?php

namespace App\Filament\Pages;

use App\Mail\LessonReminderMail;
use App\Models\LessonEnrollment;
use App\Notifications\LessonReminderNotification;
use App\Services\SmsService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class OverdueStudents extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?string $navigationLabel = 'Overdue Students';

    protected static ?string $title = 'Overdue Students';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.pages.overdue-students-fixed';

    public Collection $rows;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(
            auth()->user()?->role,
            ['admin', 'instructor'],
            true
        );
    }

    public static function canAccess(): bool
    {
        return in_array(
            auth()->user()?->role,
            ['admin', 'instructor'],
            true
        );
    }

    public function mount(): void
    {
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $this->rows = LessonEnrollment::query()
            ->with([
                'user',
                'lesson.modules.topics',
                'lesson.modules.quizzes',
                'lesson.finalQuiz',
            ])
            ->whereNotNull('enrolled_at')
            ->when(
                auth()->user()?->role === 'instructor',
                fn (Builder $query) =>
                    static::applyInstructorEnrollmentScope(
                        $query,
                        auth()->id()
                    )
            )
            ->get()
            ->map(function (LessonEnrollment $enrollment) {
                $user = $enrollment->user;
                $lesson = $enrollment->lesson;

                if (
                    ! $user
                    || ! $lesson
                    || ! $lesson->is_published
                ) {
                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | Completed lesson
                |--------------------------------------------------------------------------
                |
                | This is the source of truth.
                |
                | A lesson is complete only when:
                | - all published modules are complete
                | - required module quizzes have been attempted
                | - required final quiz has been passed
                |
                */
                if ($enrollment->is_completed) {
                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | Existing overdue rule
                |--------------------------------------------------------------------------
                |
                | Keep the current business rule:
                | student appears here only after more than 10 days.
                |
                */
                $days = $enrollment->enrolled_at
                    ->copy()
                    ->startOfDay()
                    ->diffInDays(
                        now()->startOfDay()
                    );

                if ($days <= 10) {
                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | Topic progress
                |--------------------------------------------------------------------------
                |
                | These values are for display only.
                |
                */
                $completedTopics = $enrollment->completed_topics;
                $totalTopics = $enrollment->total_topics;
                $progress = $enrollment->learning_progress_percent;

                /*
                |--------------------------------------------------------------------------
                | Module completion
                |--------------------------------------------------------------------------
                */
                $completedModules = $enrollment->completed_modules;
                $totalModules = $enrollment->total_modules;
                $modulesWithQuizPending = $enrollment->modules_with_quiz_pending;

                return [
                    'enrollment_id' => $enrollment->id,
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,

                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,

                    'lesson_title' => $lesson->title,

                    /*
                    |--------------------------------------------------------------------------
                    | Learning progress
                    |--------------------------------------------------------------------------
                    */
                    'completed_topics' => $completedTopics,
                    'total_topics' => $totalTopics,
                    'progress' => $progress,

                    /*
                    |--------------------------------------------------------------------------
                    | Real completion information
                    |--------------------------------------------------------------------------
                    */
                    'completed_modules' => $completedModules,
                    'total_modules' => $totalModules,
                    'modules_with_quiz_pending' => $modulesWithQuizPending,
                    'completion_status' => $enrollment->completion_status,
                    'completion_label' => $enrollment->completion_label,
                    'has_final_quiz_pending' => $enrollment->has_final_quiz_pending,
                    'has_any_quiz_pending' => $enrollment->has_any_quiz_pending,

                    'days' => $days,
                ];
            })
            ->filter()
            ->values();
    }

    protected static function applyInstructorEnrollmentScope(
        Builder $query,
        int $instructorId
    ): Builder {
        return $query->where(
            function (Builder $enrollmentQuery) use ($instructorId) {
                $enrollmentQuery
                    ->where(
                        'follow_up_instructor_id',
                        $instructorId
                    )
                    ->orWhereHas(
                        'lesson',
                        fn (Builder $lessonQuery) =>
                            $lessonQuery->where(
                                'lead_instructor_id',
                                $instructorId
                            )
                    )
                    ->orWhereHas(
                        'lesson',
                        function (Builder $lessonQuery) use ($instructorId) {
                            $lessonQuery
                                ->where(
                                    'instructor_id',
                                    $instructorId
                                )
                                ->whereNull(
                                    'lead_instructor_id'
                                )
                                ->whereDoesntHave(
                                    'followUpInstructors'
                                );
                        }
                    );
            }
        );
    }

    public function sendManualReminder(
        int $enrollmentId,
        string $mode = 'both'
    ): void {
        $enrollment = LessonEnrollment::query()
            ->with([
                'user',
                'lesson.modules.topics',
                'lesson.modules.quizzes',
                'lesson.finalQuiz',
            ])
            ->when(
                auth()->user()?->role === 'instructor',
                fn (Builder $query) =>
                    static::applyInstructorEnrollmentScope(
                        $query,
                        auth()->id()
                    )
            )
            ->find($enrollmentId);

        if (
            ! $enrollment
            || ! $enrollment->user
            || ! $enrollment->lesson
        ) {
            Notification::make()
                ->title('Student not found')
                ->body(
                    'This student does not exist or you do not have permission to access this record.'
                )
                ->danger()
                ->send();

            return;
        }

        $user = $enrollment->user;
        $lesson = $enrollment->lesson;

        /*
        |--------------------------------------------------------------------------
        | Do not remind completed students
        |--------------------------------------------------------------------------
        */
        if ($enrollment->is_completed) {
            Notification::make()
                ->title('Student already completed this lesson')
                ->success()
                ->send();

            $this->loadRows();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Topic progress for message display
        |--------------------------------------------------------------------------
        */
        $completedTopics = $enrollment->completed_topics;
        $totalTopics = $enrollment->total_topics;

        $emailSent = false;
        $notificationSent = false;
        $smsSent = false;

        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */
        if (
            in_array(
                $mode,
                ['email', 'both', 'email_sms', 'all'],
                true
            )
            && $user->email
        ) {
            Mail::to($user->email)->send(
                new LessonReminderMail(
                    lesson: $lesson,
                    user: $user,
                    completedTopics: $completedTopics,
                    totalTopics: $totalTopics
                )
            );

            $emailSent = true;
        }

        /*
        |--------------------------------------------------------------------------
        | In-app notification
        |--------------------------------------------------------------------------
        */
        if (
            in_array(
                $mode,
                ['notification', 'both', 'notification_sms', 'all'],
                true
            )
        ) {
            $user->notify(
                new LessonReminderNotification(
                    lesson: $lesson,
                    completedTopics: $completedTopics,
                    totalTopics: $totalTopics
                )
            );

            $notificationSent = true;
        }

        /*
        |--------------------------------------------------------------------------
        | SMS
        |--------------------------------------------------------------------------
        |
        | Give a more accurate message when topics are already 100%
        | but a quiz is still pending.
        |
        */
        if ($enrollment->has_module_quiz_pending) {
            $smsMessage =
                "Habari {$user->name}, tunakukumbusha kuendelea na somo "
                . "\"{$lesson->title}\" kwenye Uzima Milele. "
                . "Umekamilisha mada {$completedTopics}/{$totalTopics}, "
                . "lakini bado kuna jaribio la moduli linalosubiri kufanywa. "
                . "Ingia dashboard kuendelea.";
        } elseif ($enrollment->has_final_quiz_pending) {
            $smsMessage =
                "Habari {$user->name}, tunakukumbusha kukamilisha somo "
                . "\"{$lesson->title}\" kwenye Uzima Milele. "
                . "Mada zimekamilika, lakini bado unatakiwa kupita jaribio la mwisho. "
                . "Ingia dashboard kuendelea.";
        } else {
            $smsMessage =
                "Habari {$user->name}, tunakukumbusha kuendelea na somo "
                . "\"{$lesson->title}\" kwenye Uzima Milele. "
                . "Umeshakamilisha {$completedTopics}/{$totalTopics} mada. "
                . "Ingia dashboard kuendelea.";
        }

        if (
            in_array(
                $mode,
                ['sms', 'email_sms', 'notification_sms', 'all'],
                true
            )
            && $user->phone
        ) {
            $smsSent = app(SmsService::class)
                ->send(
                    $user->phone,
                    $smsMessage
                );
        }

        Notification::make()
            ->title('Manual reminder sent')
            ->body(
                'Email: '
                . ($emailSent ? 'Yes' : 'No')
                . ' | Notification: '
                . ($notificationSent ? 'Yes' : 'No')
                . ' | SMS: '
                . ($smsSent ? 'Yes' : 'No')
            )
            ->success()
            ->send();

        $this->loadRows();
    }
}
