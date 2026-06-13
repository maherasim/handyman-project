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

    // *** new: locale for translated email subject and body ***
    public $mailLocale;

        /**
     * Create a new message instance.
     */
    public function __construct(PaymentPostJOb $payment, PostJobBid $bid, $paymentType = 'advance', string $mailLocale = null)
    {
        $this->payment = $payment;
        $this->bid = $bid;
        $this->paymentType = $paymentType;
        $this->mailLocale = $mailLocale ?: app()->getLocale(); // *** new: recipient locale ***
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabel = $this->paymentType === 'advance' ? __('messages.advance') : __('messages.remaining');
        return new Envelope(
            subject: __('messages.email_subject_cash_payment_post_job_verify', ['id' => $this->bid->id, 'type' => $typeLabel], $this->mailLocale),
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

