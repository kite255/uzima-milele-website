<?php

namespace App\Notifications;

use App\Models\PrayerRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPrayerRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public PrayerRequest $prayerRequest)
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
            ->subject('Ombi Jipya la Maombi - Uzima Milele')
            ->greeting('Habari Timu ya Maombi,')
            ->line('Ombi jipya la maombi limetumwa kupitia tovuti ya Uzima Milele.')
            ->line('Jina: ' . $this->prayerRequest->name)
            ->line('Barua Pepe: ' . ($this->prayerRequest->contact ?? 'Hakuna'))
            ->line('Mada: ' . ($this->prayerRequest->prayer_type ?? 'Ombi la kawaida'))
            ->line('Faragha: ' . ($this->prayerRequest->is_private ? 'Ndiyo' : 'Hapana'))
            ->action('Fungua Ombi Admin', url('/admin/prayer-requests/' . $this->prayerRequest->id . '/edit'))
            ->line('Tafadhali lifuatilie na kuliombea kwa upendo.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ombi jipya la maombi',
            'message' => $this->prayerRequest->name . ' ametuma ombi jipya la maombi.',
            'prayer_request_id' => $this->prayerRequest->id,
            'name' => $this->prayerRequest->name,
            'contact' => $this->prayerRequest->contact,
            'subject' => $this->prayerRequest->prayer_type,
            'is_private' => $this->prayerRequest->is_private,
            'url' => url('/admin/prayer-requests/' . $this->prayerRequest->id . '/edit'),
        ];
    }
}