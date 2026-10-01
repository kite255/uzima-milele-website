<?php

namespace App\Notifications;

use App\Models\LessonQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LessonQuestionEscalatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public LessonQuestion $question,
        public string $level = 'lead'
    ) {
        $this->question->loadMissing(['lesson', 'user']);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hours = $this->level === 'admin' ? 72 : 48;

        return (new MailMessage)
            ->subject('Escalation: swali halijajibiwa - Uzima Milele')
            ->greeting('Habari ' . ($notifiable->name ?? 'Msimamizi') . ',')
            ->line('Swali la mwanafunzi limekaa zaidi ya ' . $hours . ' saa bila kujibiwa.')
            ->line('Somo: ' . ($this->question->lesson->title ?? 'Somo'))
            ->line('Mwanafunzi: ' . ($this->question->user->name ?? 'Mwanafunzi'))
            ->line('Swali:')
            ->line($this->question->question)
            ->action('Fungua Swali', route('instructor.questions.show', $this->question->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Swali lime-escalate',
            'message' => 'Swali la mwanafunzi bado halijajibiwa.',
            'question_id' => $this->question->id,
            'lesson_id' => $this->question->lesson_id,
            'level' => $this->level,
            'url' => route('instructor.questions.show', $this->question->id),
            'type' => 'lesson_question_escalated',
        ];
    }
}
