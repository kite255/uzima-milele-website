<?php

namespace App\Notifications;

use App\Models\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LessonEnrolledNotification extends Notification
{
    use Queueable;

    public function __construct(public Lesson $lesson)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $learnUrl = route('lessons.learn', $this->lesson->slug);

        return (new MailMessage)
            ->subject('Umejiunga na somo: ' . $this->lesson->title)
            ->greeting('Habari ' . ($notifiable->name ?? 'Mwanafunzi') . ',')
            ->line('Umejiunga kikamilifu na somo lifuatalo:')
            ->line('**' . $this->lesson->title . '**')
            ->action('Anza Kujifunza', $learnUrl)
            ->line('Endelea kujifunza na kukua kiroho kupitia Uzima Milele.')
            ->line('Asante kwa kuwa sehemu ya huduma hii.')
            ->salutation('Kwa upendo, Uzima Milele Ministry');
    }

    public function toArray(object $notifiable): array
    {
        $learnUrl = route('lessons.learn', $this->lesson->slug);

        return [
            'title' => 'Umejiunga na somo',
            'message' => 'Umejiunga kikamilifu na somo: ' . $this->lesson->title,
            'lesson_id' => $this->lesson->id,
            'lesson_title' => $this->lesson->title,
            'url' => $learnUrl,
            'type' => 'lesson_enrolled',
        ];
    }
}