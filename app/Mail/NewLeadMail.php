<?php

namespace App\Mail;

use App\Models\BusinessEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

/**
 * Alert sent to the team when a new business enquiry (lead) arrives.
 * Marked high priority so mail clients flag it.
 */
class NewLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BusinessEnquiry $enquiry)
    {
    }

    public function envelope(): Envelope
    {
        $source = $this->enquiry->source === 'web' ? 'Website' : 'App';

        return new Envelope(
            subject: "New lead: {$this->enquiry->business_name} ({$source})",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-lead');
    }

    public function build()
    {
        // High priority: X-Priority 1 / Importance: high (Gmail, Outlook and others show it as important).
        return $this->priority(1)->withSymfonyMessage(function (Email $message) {
            $message->getHeaders()->addTextHeader('Importance', 'high');
            $message->getHeaders()->addTextHeader('X-MSMail-Priority', 'High');
        });
    }

    public function attachments(): array
    {
        return [];
    }
}
