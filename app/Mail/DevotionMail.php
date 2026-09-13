<?php

namespace App\Mail;

use App\Models\Devotion;
use App\Models\EmailSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DevotionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Devotion $devotion,
        public ?EmailSubscriber $subscriber = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->devotion->title . ' - Morning Devotion',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.devotions.daily',
        );
    }
}