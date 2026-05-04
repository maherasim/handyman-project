<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationLink;

    public function __construct($verificationLink)
    {
        $this->verificationLink = $verificationLink;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Verify your email — ' . (string) config('app.name'))
            ->view('emails.verification');
    }
}