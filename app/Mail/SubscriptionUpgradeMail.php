<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\ProviderSubscription;

class SubscriptionUpgradeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subscription;
    public $paymentMethod;
    public $transactionId;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ProviderSubscription $subscription, string $paymentMethod, string $transactionId)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->paymentMethod = $paymentMethod;
        $this->transactionId = $transactionId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Subscription Upgrade Successful - ' . $this->subscription->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-upgrade',
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
