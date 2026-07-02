<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\PostJobBid;

class PostJobBidStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;
    public $bid;
    public $oldStatus;
    public $newStatus;
    public $actorName;
    public $actorType;
    public $recipientType;
    public $mailLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $recipient, PostJobBid $bid, $oldStatus, $newStatus, $actorName, $actorType, $recipientType = null, $mailLocale = null)
    {
        $this->recipient = $recipient;
        $this->bid = $bid;
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
        $statusKey = 'messages.pjr_st_' . $this->newStatus;
        $statusLabel = \Illuminate\Support\Facades\Lang::has($statusKey, $this->mailLocale)
            ? __($statusKey, [], $this->mailLocale)
            : ucwords(str_replace('_', ' ', $this->newStatus));

        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('messages.email_subject_post_job_bid_status', ['title' => optional($this->bid->postrequest)->title ?? '', 'status' => $statusLabel], $this->mailLocale),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.post-job-bid-status-update',
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
