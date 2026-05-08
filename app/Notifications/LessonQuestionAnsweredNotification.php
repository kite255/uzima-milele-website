<?php

namespace App\Notifications;

use App\Models\LessonQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LessonQuestionAnsweredNotification extends Notification
{
    use Queueable;

    public $question;

    public function __construct(LessonQuestion $question)
    {
        $this->question = $question;
        $this->question->loadMissing(['lesson', 'user']);
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        $lesson = $this->question->lesson;

        $lessonTitle = $lesson ? $lesson->title : 'Somo';
        $lessonSlug = $lesson ? $lesson->slug : null;

        return [
            'title' => 'Swali lako limejibiwa',
            'message' => 'Swali lako kuhusu somo "' . $lessonTitle . '" limejibiwa.',
            'lesson_id' => $this->question->lesson_id,
            'question_id' => $this->question->id,
            'url' => $lessonSlug
                ? route('lessons.show', $lessonSlug) . '#questions'
                : route('student.dashboard'),
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function toMail($notifiable): MailMessage
    {
        $lesson = $this->question->lesson;

        $lessonTitle = $lesson ? $lesson->title : 'Somo la Biblia';
        $lessonSlug = $lesson ? $lesson->slug : null;

        $url = $lessonSlug
            ? route('lessons.show', $lessonSlug) . '#questions'
            : route('student.dashboard');

        return (new MailMessage)
            ->subject('Swali lako limejibiwa - Uzima Milele')
            ->greeting('Habari ' . ($notifiable->name ?? 'Mwanafunzi') . ',')
            ->line('Swali lako kuhusu somo "' . $lessonTitle . '" limejibiwa.')
            ->line('Swali ulilouliza:')
            ->line($this->question->question ?: 'Swali lako')
            ->line('Jibu:')
            ->line($this->question->answer ?: 'Jibu limewekwa kwenye akaunti yako.')
            ->action('Tazama Jibu', $url)
            ->line('Asante kwa kujifunza kupitia Uzima Milele Ministry.');
    }
}