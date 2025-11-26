<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class NotificationReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $stats;
    public $type;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification, array $stats, string $type = 'completed')
    {
        $this->notification = $notification;
        $this->stats = $stats;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->type === 'started' 
            ? "🚀 Notification Started: {$this->notification->title}"
            : "📊 Notification Report: {$this->notification->title}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification-report',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

