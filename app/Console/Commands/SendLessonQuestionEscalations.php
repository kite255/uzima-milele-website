<?php

namespace App\Console\Commands;

use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\User;
use App\Notifications\LessonQuestionEscalatedNotification;
use App\Notifications\LessonQuestionReminderNotification;
use Illuminate\Console\Command;

class SendLessonQuestionEscalations extends Command
{
    protected $signature = 'lessons:send-question-escalations';

    protected $description = 'Send reminders and escalation emails for unanswered lesson questions';

    public function handle(): int
    {
        LessonQuestion::query()
            ->pending()
            ->whereNull('answered_at')
            ->where('created_at', '<=', now()->subHours(24))
            ->with(['lesson.leadInstructor', 'lesson.instructor', 'user'])
            ->chunkById(100, function ($questions): void {
                foreach ($questions as $question) {
                    $this->processQuestion($question);
                }
            });

        return self::SUCCESS;
    }

    private function processQuestion(LessonQuestion $question): void
    {
        $enrollment = LessonEnrollment::query()
            ->with('followUpInstructor')
            ->where('lesson_id', $question->lesson_id)
            ->where('user_id', $question->user_id)
            ->first();

        $followUp = $enrollment?->followUpInstructor;
        $lead = $question->lesson?->leadInstructor;
        $legacy = $question->lesson?->instructor;

        if (! $question->instructor_reminded_at) {
            $recipient = $followUp ?: $lead ?: $legacy;

            if ($recipient) {
                $recipient->notify(new LessonQuestionReminderNotification($question));
                $question->forceFill(['instructor_reminded_at' => now()])->save();
            }
        }

        if (
            $question->created_at->lte(now()->subHours(48))
            && ! $question->lead_escalated_at
        ) {
            $recipient = $lead ?: $legacy;

            if ($recipient && (! $followUp || $recipient->id !== $followUp->id)) {
                $recipient->notify(new LessonQuestionEscalatedNotification($question, 'lead'));
            }

            $question->forceFill(['lead_escalated_at' => now()])->save();
        }

        if (
            $question->created_at->lte(now()->subHours(72))
            && ! $question->admin_escalated_at
        ) {
            User::query()
                ->where('role', 'admin')
                ->get()
                ->each(fn (User $admin) => $admin->notify(
                    new LessonQuestionEscalatedNotification($question, 'admin')
                ));

            $question->forceFill(['admin_escalated_at' => now()])->save();
        }
    }
}
