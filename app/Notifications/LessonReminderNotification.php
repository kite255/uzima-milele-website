<?php

namespace App\Notifications;

use App\Models\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LessonReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Lesson $lesson,
        public int $completedTopics,
        public int $totalTopics
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Endelea na somo lako',
            'message' => 'Bado hujakamilisha somo "' . $this->lesson->title . '". Endelea kujifunza.',
            'lesson_id' => $this->lesson->id,
            'lesson_title' => $this->lesson->title,
            'completed_topics' => $this->completedTopics,
            'total_topics' => $this->totalTopics,
            'url' => route('lessons.learn', $this->lesson->slug),
        ];
    }
}