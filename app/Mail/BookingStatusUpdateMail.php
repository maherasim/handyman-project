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
    public $recipientType;
    public $mailLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $recipient, Booking $booking, $oldStatus, $newStatus, $actorName, $actorType, $recipientType = null, $mailLocale = null)
    {
        $this->recipient = $recipient;
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->actorName = $actorName;
        $this->actorType = $actorType;
        $this->recipientType = $recipientType;
        $this->mailLocale = $mailLocale ?: app()->getLocale();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        $statusKey = 'messages.booking_status_option_' . $this->newStatus;
        $statusLabel = \Illuminate\Support\Facades\Lang::has($statusKey, $this->mailLocale)
            ? __($statusKey, [], $this->mailLocale)
            : ucwords(str_replace('_', ' ', $this->newStatus));

        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.booking_status_email_subject', ['id' => $this->booking->id, 'status' => $statusLabel], $this->mailLocale),
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
