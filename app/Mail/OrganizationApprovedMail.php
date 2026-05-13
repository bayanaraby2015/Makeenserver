<?php

namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Organization $organization) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.organization.approved.subject', [
                'name' => $this->organization->name_ar,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.organization.approved',
            with: [
                'organization' => $this->organization,
                'loginUrl' => url(config('brand.login_url', '/association/login')),
            ],
        );
    }
}
