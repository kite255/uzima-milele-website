<?php

namespace App\Mail;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LessonReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lesson $lesson,
        public User $user,
        public int $completedTopics,
        public int $totalTopics
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Endelea na somo lako - Uzima Milele',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lesson-reminder',
        );
    }
}