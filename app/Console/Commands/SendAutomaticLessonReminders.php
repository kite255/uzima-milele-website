<?php

namespace App\Console\Commands;

use App\Mail\LessonReminderMail;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
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

        $enrollments = LessonEnrollment::with([
                'user',
                'lesson.modules.topics',
            ])
            ->whereNotNull('enrolled_at')
            ->get();

        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            $lesson = $enrollment->lesson;

            if (! $user || ! $lesson || ! $lesson->is_published || ! $enrollment->enrolled_at) {
                $skipped++;
                continue;
            }

            $daysSinceEnrollment = $enrollment->enrolled_at
                ->copy()
                ->startOfDay()
                ->diffInDays(now()->startOfDay());

            if (! in_array($daysSinceEnrollment, $reminderDays)) {
                $skipped++;
                continue;
            }

            $totalTopics = $lesson->modules
                ->where('is_published', true)
                ->flatMap(fn ($module) => $module->topics->where('is_published', true))
                ->count();

            if ($totalTopics <= 0) {
                $skipped++;
                continue;
            }

            $completedTopics = LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->distinct('lesson_topic_id')
                ->count('lesson_topic_id');

            if ($completedTopics >= $totalTopics) {
                $skipped++;
                continue;
            }

            $alreadySent = LessonReminderLog::where('lesson_id', $lesson->id)
                ->where('user_id', $user->id)
                ->where('reminder_day', $daysSinceEnrollment)
                ->exists();

            if ($alreadySent) {
                $skipped++;
                continue;
            }

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

            $user->notify(
                new LessonReminderNotification(
                    lesson: $lesson,
                    completedTopics: $completedTopics,
                    totalTopics: $totalTopics
                )
            );

            LessonReminderLog::create([
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'reminder_day' => $daysSinceEnrollment,
                'mode' => 'both',
                'sent_at' => now(),
            ]);

            $sent++;
        }

        $this->info("Automatic reminders sent: {$sent}");
        $this->info("Skipped: {$skipped}");
        $this->info('SMS not used. SMS is manual only.');

        return self::SUCCESS;
    }
}