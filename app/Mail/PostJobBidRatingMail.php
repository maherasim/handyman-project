<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\PostJobBid;

class PostJobBidRatingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;
    public $bid;
    public $rater;
    public $rating;
    public $review;
    public $recipientType; // 'provider' | 'user'
    public $mailLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $recipient, PostJobBid $bid, User $rater, int $rating, string $review, string $recipientType, string $mailLocale = null)
    {
        $this->recipient = $recipient;
        $this->bid = $bid;
        $this->rater = $rater;
        $this->rating = $rating;
        $this->review = $review;
        $this->recipientType = $recipientType;
        $this->mailLocale = $mailLocale ?: app()->getLocale();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.email_subject_post_job_bid_rated', ['title' => optional($this->bid->postrequest)->title ?? ''], $this->mailLocale),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.post-job-bid-rating',
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
