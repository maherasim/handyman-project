<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Payment;
use App\Models\Booking;

class AdvancePaymentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $provider;
    public $payment;
    public $booking;
    public $advanceAmount;

    /**
     * Create a new message instance.
     */
    public function __construct(User $provider, Payment $payment, Booking $booking)
    {
        $this->provider = $provider;
        $this->payment = $payment;
        $this->booking = $booking;
        $this->advanceAmount = $payment->total_amount ?? $booking->advance_paid_amount ?? 0;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.email_subject_advance_payment_received', ['id' => $this->booking->id]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.advance-payment-notification',
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

