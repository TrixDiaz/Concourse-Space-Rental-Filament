<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaseContractMail extends Mailable
{
    use Queueable, SerializesModels;

    public $owner;
    public $tenantUser;
    public $tenant;
    public $space;
    public $application;

    /**
     * Create a new message instance.
     */
    public function __construct($owner, $tenantUser, $tenant, $space, $application)
    {
        $this->owner = $owner;
        $this->tenantUser = $tenantUser;
        $this->tenant = $tenant;
        $this->space = $space;
        $this->application = $application;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lease Contract',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.lease-contract',
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
