<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuizbeeSessionSummary extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, object> $answers
     */
    public function __construct(
        public string $studentName,
        public array $answers,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Quizbee Session History',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quizbee_session_summary');
    }

    public function attachments(): array
    {
        return [];
    }
}
