<?php

namespace App\Mail;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LessonEnrollmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Lesson $lesson
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Umejiunga na somo: ' . $this->lesson->title
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lessons.enrolled'
        );
    }
}