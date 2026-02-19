<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PaymentPostJOb;
use App\Models\PostJobBid;

class PostJobBankTransferPaymentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $bid;
    public $paymentType;

    /**
     * Create a new message instance.
     */
    public function __construct(PaymentPostJOb $payment, PostJobBid $bid, $paymentType = 'advance')
    {
        $this->payment = $payment;
        $this->bid = $bid;
        $this->paymentType = $paymentType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabel = $this->paymentType === 'advance' ? 'Advance' : 'Remaining';
        return new Envelope(
            subject: 'Cash payment submitted – Post Job Bid #' . $this->bid->id . ' (' . $typeLabel . ') – please verify',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.post-job-bank-transfer-payment-notification',
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

