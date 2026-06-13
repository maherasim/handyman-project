<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChatPiiWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    // *** new: locale for translated subject and body ***
    public $mailLocale;

    public function __construct(array $mailData, string $mailLocale = null)
    {
        $this->mailData = $mailData;
        $this->mailLocale = $mailLocale ?: app()->getLocale(); // *** new: recipient locale ***
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject(__('messages.chat_policy_warning_email_subject', [], $this->mailLocale))
            ->view('emails.chat_pii_warning')
            ->with(array_merge($this->mailData, ['mailLocale' => $this->mailLocale]));
    }
}
