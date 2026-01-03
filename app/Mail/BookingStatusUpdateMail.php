<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Booking;

class BookingStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;
    public $booking;
    public $oldStatus;
    public $newStatus;
    public $actorName;
    public $actorType;

    /**
     * Create a new message instance.
     */
    public function __construct(User $recipient, Booking $booking, $oldStatus, $newStatus, $actorName, $actorType)
    {
        $this->recipient = $recipient;
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->actorName = $actorName;
        $this->actorType = $actorType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        $statusLabel = ucwords(str_replace('_', ' ', $this->newStatus));
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: '📋 Booking Status Updated - Booking #' . $this->booking->id . ' - ' . $statusLabel,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.booking-status-update',
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

