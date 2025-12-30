<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Payment;
use App\Models\Booking;

class FullPaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $provider;
    public $payment;
    public $booking;
    public $totalAmount;
    public $advanceAmount;
    public $remainingAmount;

    /**
     * Create a new message instance.
     */
    public function __construct(User $provider, Payment $payment, Booking $booking)
    {
        $this->provider = $provider;
        $this->payment = $payment;
        $this->booking = $booking;
        $this->totalAmount = $booking->total_amount ?? 0;
        $this->advanceAmount = $booking->advance_paid_amount ?? 0;
        $this->remainingAmount = $this->totalAmount - $this->advanceAmount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        $providerName = $this->provider->display_name ?? $this->provider->first_name ?? 'Provider';
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: '🎉 Full Payment Received - Booking #' . $this->booking->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.full-payment-received',
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

