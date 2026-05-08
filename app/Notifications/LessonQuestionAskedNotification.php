<?php

namespace App\Notifications;

use App\Models\LessonQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LessonQuestionAskedNotification extends Notification
{
    use Queueable;

    public function __construct(public LessonQuestion $question)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Swali jipya limeulizwa - Uzima Milele')
            ->greeting('Habari ' . ($notifiable->name ?? 'Mwalimu') . ',')
            ->line(($this->question->user->name ?? 'Mwanafunzi') . ' ameuliza swali kwenye somo:')
            ->line($this->question->lesson->title ?? 'Somo')
            ->line('Swali:')
            ->line($this->question->question)
            ->action('Jibu Swali', route('instructor.questions.show', $this->question->id))
            ->line('Tafadhali jibu swali hili ili kuwasaidia wanafunzi kujifunza vizuri.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Swali jipya limeulizwa',
            'message' => ($this->question->user->name ?? 'Mwanafunzi') . ' ameuliza swali kwenye somo: ' . ($this->question->lesson->title ?? 'Somo'),
            'lesson_id' => $this->question->lesson_id,
            'question_id' => $this->question->id,
            'lesson_title' => $this->question->lesson->title ?? null,
            'student_name' => $this->question->user->name ?? null,
            'url' => route('instructor.questions.show', $this->question->id),
            'type' => 'lesson_question_asked',
        ];
    }
}