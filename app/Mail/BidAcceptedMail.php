<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\PostJobBid;

class BidAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $provider;
    public $bid;
    public $customer;
    public $mailLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $provider, PostJobBid $bid, User $customer, string $mailLocale = null)
    {
        $this->provider = $provider;
        $this->bid = $bid;
        $this->customer = $customer;
        $this->mailLocale = $mailLocale ?: app()->getLocale();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.email_subject_bid_accepted', ['title' => optional($this->bid->postrequest)->title ?? ''], $this->mailLocale),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.bid-accepted',
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
