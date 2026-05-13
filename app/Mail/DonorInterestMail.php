<?php

namespace App\Mail;

use App\Models\DonorInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonorInterestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public DonorInterest $interest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.donor_interest.subject', [
                'initiative' => $this->interest->initiative->name_ar ?? '',
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.donor_interest.received',
            with: [
                'interest' => $this->interest,
                'initiative' => $this->interest->initiative,
                'donor' => $this->interest->user,
            ],
        );
    }
}
