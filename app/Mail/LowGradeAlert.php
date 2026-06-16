<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowGradeAlert extends Mailable
{
    use Queueable, SerializesModels;

    public string $studentName;
    public string $studentEmail;
    public string $subjectName;
    public string $type;
    public float  $grade;

    /**
     * Create a new message instance.
     */
    public function __construct(string $studentName, string $studentEmail, string $subjectName, string $type, float $grade)
    {
        $this->studentName  = $studentName;
        $this->studentEmail = $studentEmail;
        $this->subjectName  = $subjectName;
        $this->type         = $type;
        $this->grade        = $grade;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Grade Alert – ' . strtoupper($this->subjectName) . ' (' . $this->grade . '%)',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.low_grade_alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
