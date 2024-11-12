<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationApproveMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Approved',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.application_approve',
            with: [
                'greeting' => 'Dear ' . $this->mailData['tenantName'],
                'line1' => 'We are pleased to inform you that your renewal application for ' . $this->mailData['spaceName'] . ' in ' . $this->mailData['concourseName'] . ' at ' . $this->mailData['concourseAddress'] . ' has been approved. Your lease has been successfully renewed, and you may continue your tenancy without interruption.',
                'line2' => 'Thank you for choosing to stay with us. If you have any further questions, please feel free to reach out.',
                'salutation' => 'Regards,',
                'fromName' => 'COMS'
            ]
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
