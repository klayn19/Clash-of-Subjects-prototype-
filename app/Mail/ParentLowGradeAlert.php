<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParentLowGradeAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $subjectName,
        public string $type,
        public float $grade,
        public ?int $quarter,
        public float $correct,
        public float $total,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Failing Score Alert – ' . $this->studentName,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.parent_low_grade_alert');
    }

    public function attachments(): array
    {
        return [];
    }
}
