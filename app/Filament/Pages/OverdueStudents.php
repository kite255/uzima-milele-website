<?php

namespace App\Filament\Pages;

use App\Mail\LessonReminderMail;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
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
        return in_array(auth()->user()?->role, ['admin', 'instructor']);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'instructor']);
    }

    public function mount(): void
    {
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $this->rows = LessonEnrollment::with([
                'user',
                'lesson.modules.topics',
            ])
            ->whereNotNull('enrolled_at')
            ->when(auth()->user()?->role === 'instructor', function (Builder $query) {
                $query->whereHas('lesson', function (Builder $lessonQuery) {
                    $lessonQuery->where('instructor_id', auth()->id());
                });
            })
            ->get()
            ->map(function ($enrollment) {
                $user = $enrollment->user;
                $lesson = $enrollment->lesson;

                if (! $user || ! $lesson || ! $lesson->is_published) {
                    return null;
                }

                $days = $enrollment->enrolled_at
                    ->copy()
                    ->startOfDay()
                    ->diffInDays(now()->startOfDay());

                if ($days <= 10) {
                    return null;
                }

                $totalTopics = $lesson->modules
                    ->where('is_published', true)
                    ->flatMap(fn ($module) => $module->topics->where('is_published', true))
                    ->count();

                if ($totalTopics <= 0) {
                    return null;
                }

                $completedTopics = LessonProgress::where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->distinct('lesson_topic_id')
                    ->count('lesson_topic_id');

                if ($completedTopics >= $totalTopics) {
                    return null;
                }

                $progress = round(($completedTopics / $totalTopics) * 100);

                return [
                    'enrollment_id' => $enrollment->id,
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'lesson_title' => $lesson->title,
                    'completed_topics' => $completedTopics,
                    'total_topics' => $totalTopics,
                    'progress' => $progress,
                    'days' => $days,
                ];
            })
            ->filter()
            ->values();
    }

    public function sendManualReminder(int $enrollmentId, string $mode = 'both'): void
    {
        $enrollment = LessonEnrollment::with([
                'user',
                'lesson.modules.topics',
            ])
            ->when(auth()->user()?->role === 'instructor', function (Builder $query) {
                $query->whereHas('lesson', function (Builder $lessonQuery) {
                    $lessonQuery->where('instructor_id', auth()->id());
                });
            })
            ->find($enrollmentId);

        if (! $enrollment || ! $enrollment->user || ! $enrollment->lesson) {
            Notification::make()
                ->title('Student not found')
                ->body('This student does not exist or you do not have permission to access this record.')
                ->danger()
                ->send();

            return;
        }

        $user = $enrollment->user;
        $lesson = $enrollment->lesson;

        $totalTopics = $lesson->modules
            ->where('is_published', true)
            ->flatMap(fn ($module) => $module->topics->where('is_published', true))
            ->count();

        $completedTopics = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->distinct('lesson_topic_id')
            ->count('lesson_topic_id');

        if ($completedTopics >= $totalTopics) {
            Notification::make()
                ->title('Student already completed this lesson')
                ->success()
                ->send();

            $this->loadRows();

            return;
        }

        $emailSent = false;
        $notificationSent = false;
        $smsSent = false;

        if (in_array($mode, ['email', 'both', 'email_sms', 'all']) && $user->email) {
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

        if (in_array($mode, ['notification', 'both', 'notification_sms', 'all'])) {
            $user->notify(
                new LessonReminderNotification(
                    lesson: $lesson,
                    completedTopics: $completedTopics,
                    totalTopics: $totalTopics
                )
            );

            $notificationSent = true;
        }

        $smsMessage = "Habari {$user->name}, tunakukumbusha kuendelea na somo \"{$lesson->title}\" kwenye Uzima Milele. Umeshakamilisha {$completedTopics}/{$totalTopics} mada. Ingia dashboard kuendelea.";

        if (in_array($mode, ['sms', 'email_sms', 'notification_sms', 'all']) && $user->phone) {
            $smsSent = app(SmsService::class)->send($user->phone, $smsMessage);
        }

        Notification::make()
            ->title('Manual reminder sent')
            ->body('Email: ' . ($emailSent ? 'Yes' : 'No') . ' | Notification: ' . ($notificationSent ? 'Yes' : 'No') . ' | SMS: ' . ($smsSent ? 'Yes' : 'No'))
            ->success()
            ->send();

        $this->loadRows();
    }
}