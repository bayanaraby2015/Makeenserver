<?php

namespace App\Mail;

use App\Models\Initiative;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InitiativeApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Initiative $initiative) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.initiative.approved.subject', [
                'name' => $this->initiative->name_ar,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.initiative.approved',
            with: [
                'initiative' => $this->initiative,
                'organization' => $this->initiative->organization,
                'loginUrl' => url('/association/login'),
            ],
        );
    }
}
