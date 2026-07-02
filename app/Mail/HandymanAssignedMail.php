<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Booking;

class HandymanAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $handyman;
    public $booking;
    public $provider;
    public $mailLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $handyman, Booking $booking, User $provider, string $mailLocale = null)
    {
        $this->handyman = $handyman;
        $this->booking = $booking;
        $this->provider = $provider;
        $this->mailLocale = $mailLocale ?: app()->getLocale();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.email_subject_handyman_assigned', ['id' => $this->booking->id], $this->mailLocale),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.handyman-assigned',
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
