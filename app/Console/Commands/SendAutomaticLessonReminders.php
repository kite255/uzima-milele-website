<?php

namespace App\Console\Commands;

use App\Mail\LessonReminderMail;
use App\Models\LessonEnrollment;
use App\Models\LessonReminderLog;
use App\Notifications\LessonReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAutomaticLessonReminders extends Command
{
    protected $signature = 'lessons:send-automatic-reminders';

    protected $description = 'Send automatic lesson reminders on day 2, 5, and 10 using email and dashboard notifications only.';

    public function handle(): int
    {
        $reminderDays = [2, 5, 10];

        $sent = 0;
        $skipped = 0;

        $enrollments = LessonEnrollment::query()
            ->with([
                'user',
                'lesson.modules.topics',
                'lesson.modules.quizzes',
                'lesson.finalQuiz',
            ])
            ->whereNotNull('enrolled_at')
            ->get();

        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            $lesson = $enrollment->lesson;

            /*
            |--------------------------------------------------------------------------
            | Basic validation
            |--------------------------------------------------------------------------
            */
            if (
                ! $user
                || ! $lesson
                || ! $lesson->is_published
                || ! $enrollment->enrolled_at
            ) {
                $skipped++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Stop reminders for fully completed lessons
            |--------------------------------------------------------------------------
            |
            | This is now the source of truth.
            |
            | A student may have 100% topic progress but still be incomplete
            | because:
            |
            | - a required module quiz has not been attempted, or
            | - a required final quiz has not been passed.
            |
            */
            if ($enrollment->is_completed) {
                $skipped++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Reminder day
            |--------------------------------------------------------------------------
            */
            $daysSinceEnrollment = $enrollment->enrolled_at
                ->copy()
                ->startOfDay()
                ->diffInDays(
                    now()->startOfDay()
                );

            if (
                ! in_array(
                    $daysSinceEnrollment,
                    $reminderDays,
                    true
                )
            ) {
                $skipped++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Learning progress
            |--------------------------------------------------------------------------
            |
            | Topic counts are used only for the reminder content.
            | They are NOT used to decide whole-lesson completion.
            |
            */
            $totalTopics = $enrollment->total_topics;
            $completedTopics = $enrollment->completed_topics;

            if ($totalTopics <= 0) {
                $skipped++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate reminder
            |--------------------------------------------------------------------------
            */
            $alreadySent = LessonReminderLog::query()
                ->where(
                    'lesson_id',
                    $lesson->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'reminder_day',
                    $daysSinceEnrollment
                )
                ->exists();

            if ($alreadySent) {
                $skipped++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Email
            |--------------------------------------------------------------------------
            */
            if ($user->email) {
                Mail::to($user->email)->send(
                    new LessonReminderMail(
                        lesson: $lesson,
                        user: $user,
                        completedTopics: $completedTopics,
                        totalTopics: $totalTopics
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Dashboard notification
            |--------------------------------------------------------------------------
            */
            $user->notify(
                new LessonReminderNotification(
                    lesson: $lesson,
                    completedTopics: $completedTopics,
                    totalTopics: $totalTopics
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Reminder log
            |--------------------------------------------------------------------------
            */
            LessonReminderLog::create([
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'reminder_day' => $daysSinceEnrollment,
                'mode' => 'both',
                'sent_at' => now(),
            ]);

            $sent++;
        }

        $this->info(
            "Automatic reminders sent: {$sent}"
        );

        $this->info(
            "Skipped: {$skipped}"
        );

        $this->info(
            'SMS not used. SMS is manual only.'
        );

        return self::SUCCESS;
    }
}