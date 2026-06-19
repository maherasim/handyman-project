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

    public function __construct(User $handyman, User $provider, string $plainPassword)
    {
        $this->handyman      = $handyman;
        $this->provider      = $provider;
        $this->plainPassword = $plainPassword;
        $this->loginUrl      = url('/login');
    }

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.handyman_credentials_email_subject', [], $this->handyman->lang ?? app()->getLocale()),
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
