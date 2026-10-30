<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $teacherName,
        public string $subjectName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Clash of Subject Teacher Account',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.teacher_welcome');
    }

    public function attachments(): array
    {
        return [];
    }
}
