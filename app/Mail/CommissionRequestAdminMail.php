<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CommissionRequestAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $handyman;
    public User $provider;
    public float $currentCommission;
    public float $requestedCommission;
    public string $reason;
    public string $reviewUrl;

    public function __construct(User $handyman, User $provider, float $currentCommission, float $requestedCommission, string $reason = '')
    {
        $this->handyman            = $handyman;
        $this->provider            = $provider;
        $this->currentCommission   = $currentCommission;
        $this->requestedCommission = $requestedCommission;
        $this->reason              = $reason ?: '—';
        $this->reviewUrl           = route('commission-request.index');
    }

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.commission_request_admin_email_subject', [
                'handyman' => $this->handyman->display_name,
            ]),
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.commission-request-admin',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
