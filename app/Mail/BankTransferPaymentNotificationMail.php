<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;
use App\Models\Booking;

class BankTransferPaymentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $booking;
    public $paymentType;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, Booking $booking, $paymentType = 'payment')
    {
        $this->payment = $payment;
        $this->booking = $booking;
        $this->paymentType = $paymentType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabel = $this->paymentType === 'advance_payment' ? 'Advance' : 'Remaining';
        return new Envelope(
            subject: 'Cash payment submitted – Booking #' . $this->booking->id . ' (' . $typeLabel . ') – please verify',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bank-transfer-payment-notification',
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

