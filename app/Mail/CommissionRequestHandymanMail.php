<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CommissionRequestHandymanMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $handyman;
    public User $provider;
    public float $currentCommission;
    public float $requestedCommission;
    public string $reason;
    public string $helpdeskUrl;

    public function __construct(User $handyman, User $provider, float $currentCommission, float $requestedCommission, int $helpdeskId, string $reason = '')
    {
        $this->handyman            = $handyman;
        $this->provider            = $provider;
        $this->currentCommission   = $currentCommission;
        $this->requestedCommission = $requestedCommission;
        $this->reason              = $reason ?: '—';
        $this->helpdeskUrl         = route('helpdesk.show', $helpdeskId);
    }

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.commission_request_handyman_email_subject', [], $this->handyman->lang ?? app()->getLocale()),
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.commission-request-handyman',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
