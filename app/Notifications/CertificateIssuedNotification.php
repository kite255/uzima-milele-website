<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification
{
    use Queueable;

    public function __construct(public Certificate $certificate)
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
            ->subject('Cheti chako kimetolewa - Uzima Milele')
            ->greeting('Habari ' . ($notifiable->name ?? 'Mwanafunzi') . ',')
            ->line('Hongera! Cheti chako kimetolewa kwa kukamilisha somo:')
            ->line($this->certificate->lesson->title ?? 'Somo')
            ->line('Namba ya Cheti: ' . $this->certificate->certificate_number)
            ->action('Tazama Cheti', route('certificates.show', $this->certificate->certificate_number))
            ->line('Endelea kujifunza na kukua kiroho kupitia Uzima Milele.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Cheti kimetolewa',
            'message' => 'Cheti chako cha somo "' . ($this->certificate->lesson->title ?? 'Somo') . '" kimetolewa.',
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
            'lesson_id' => $this->certificate->lesson_id,
            'lesson_title' => $this->certificate->lesson->title ?? null,
            'url' => route('certificates.show', $this->certificate->certificate_number),
            'type' => 'certificate_issued',
        ];
    }
}