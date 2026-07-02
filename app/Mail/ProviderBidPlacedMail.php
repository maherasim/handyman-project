<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\PostJobBid;
use App\Models\PostJobRequest;

class ProviderBidPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $bid;
    public $postJob;
    public $provider;
    public $mailLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $customer, PostJobBid $bid, PostJobRequest $postJob, User $provider, string $mailLocale = null)
    {
        $this->customer = $customer;
        $this->bid = $bid;
        $this->postJob = $postJob;
        $this->provider = $provider;
        $this->mailLocale = $mailLocale ?: app()->getLocale();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.email_subject_provider_bid_placed', ['title' => $this->postJob->title], $this->mailLocale),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.provider-bid-placed',
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
