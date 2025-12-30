<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage;
use App\Models\User;

class ChatMessageNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;
    public $sender;
    public $chatMessage;
    public $conversation;

    /**
     * Create a new message instance.
     */
    public function __construct(User $recipient, User $sender, ChatMessage $message, $conversation)
    {
        $this->recipient = $recipient;
        $this->sender = $sender;
        $this->chatMessage = $message;
        $this->conversation = $conversation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $senderName = $this->sender->display_name ?? $this->sender->first_name ?? 'Someone';
        return new Envelope(
            subject: '💬 New Message from ' . $senderName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.chat-message-notification',
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

