<?php

namespace App\Notifications;

use App\Models\LessonQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LessonQuestionReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public LessonQuestion $question)
    {
        $this->question->loadMissing(['lesson', 'user']);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kumbusho: swali la mwanafunzi halijajibiwa - Uzima Milele')
            ->greeting('Habari ' . ($notifiable->name ?? 'Mwalimu') . ',')
            ->line(($this->question->user->name ?? 'Mwanafunzi') . ' bado anasubiri jibu kwenye somo:')
            ->line($this->question->lesson->title ?? 'Somo')
            ->line('Swali:')
            ->line($this->question->question)
            ->action('Jibu Swali', route('instructor.questions.show', $this->question->id))
            ->line('Tafadhali jibu swali hili haraka iwezekanavyo.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Kumbusho la swali lisilojibiwa',
            'message' => ($this->question->user->name ?? 'Mwanafunzi') . ' bado anasubiri jibu.',
            'question_id' => $this->question->id,
            'lesson_id' => $this->question->lesson_id,
            'url' => route('instructor.questions.show', $this->question->id),
            'type' => 'lesson_question_reminder',
        ];
    }
}
