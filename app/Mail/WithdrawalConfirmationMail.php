<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\WithdrawMoney;
use App\Models\User;

class WithdrawalConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $withdrawal;
    public $bank;

    // *** new: locale for translated email subject and body ***
    public $mailLocale;

        /**
     * Create a new message instance.
     */
    public function __construct(User $user, WithdrawMoney $withdrawal, string $mailLocale = null)
    {
        $this->user = $user;
        $this->withdrawal = $withdrawal;
        $this->bank = $withdrawal->bank;
        $this->mailLocale = $mailLocale ?: app()->getLocale(); // *** new: recipient locale ***
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.email_subject_withdrawal_confirmed', ['amount' => getPriceFormat($this->withdrawal->amount)], $this->mailLocale),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.withdrawal-confirmation',
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

