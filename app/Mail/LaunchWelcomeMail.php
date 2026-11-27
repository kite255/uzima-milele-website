<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
class LaunchWelcomeMail extends Mailable {
    public function __construct(public string $attendeeName) {}
    public function envelope(): Envelope { return new Envelope(subject:'Karibu katika Familia ya Uzima Milele!'); }
    public function content(): Content { return new Content(view:'emails.launch-welcome'); }
}
