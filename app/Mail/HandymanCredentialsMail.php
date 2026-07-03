<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class HandymanCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $handyman;
    public User $provider;
    public string $plainPassword;
    public string $loginUrl;
    public string $mailLocale;

    public function __construct(User $handyman, User $provider, string $plainPassword, string $mailLocale = null)
    {
        $this->handyman      = $handyman;
        $this->provider      = $provider;
        $this->plainPassword = $plainPassword;
        $this->loginUrl      = url('/login');
        $this->mailLocale    = $mailLocale ?: app()->getLocale();
    }

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.handyman_credentials_email_subject', [], $this->mailLocale),
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.handyman-credentials',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
