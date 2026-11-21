<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonQuestion;
use App\Models\LessonTopic;
use App\Models\User;
use App\Notifications\LessonQuestionAskedNotification;
use Throwable;

use function Illuminate\Support\defer;

class AskLessonQuestion
{
    public const QUESTION_RULES = ['required', 'string', 'min:5', 'max:1000'];

    public function __construct(private readonly LessonQuestionRecipients $recipients) {}

    public function submit(
        LessonEnrollment $enrollment,
        Lesson $lesson,
        string $body,
        ?LessonTopic $topic = null,
    ): LessonQuestion {
        $question = LessonQuestion::create([
            'lesson_id' => $lesson->id,
            'lesson_topic_id' => $topic?->id,
            'user_id' => $enrollment->user_id,
            'question' => $body,
            'status' => LessonQuestion::STATUS_PENDING,
            'visibility' => LessonQuestion::VISIBILITY_PRIVATE,
            'is_published' => true,
        ]);

        $question->setRelation('lesson', $lesson)->setRelation('lessonTopic', $topic);

        defer(function () use ($question, $enrollment, $lesson): void {
            $question->loadMissing('user');
            $assigned = $this->recipients->followUp($enrollment) ?? $this->recipients->courseInstructor($lesson);
            $recipients = $assigned ? [$assigned] : User::query()->where('role', 'admin')->get();

            foreach ($recipients as $recipient) {
                try {
                    $recipient->notify(new LessonQuestionAskedNotification($question));
                } catch (Throwable $error) {
                    // Keep the question available in the inbox if notification delivery fails.
                    report($error);
                }
            }
        });

        return $question;
    }
}
