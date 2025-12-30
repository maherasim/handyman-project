<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Booking;

class ServiceBookingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $provider;
    public $booking;
    public $customer;

    /**
     * Create a new message instance.
     */
    public function __construct(User $provider, Booking $booking, User $customer)
    {
        $this->provider = $provider;
        $this->booking = $booking;
        $this->customer = $customer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        $providerName = $this->provider->display_name ?? $this->provider->first_name ?? 'Provider';
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: '📅 New Service Booking Received - Booking #' . $this->booking->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.service-booking-notification',
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

