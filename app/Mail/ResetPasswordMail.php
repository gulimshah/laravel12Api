<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationCode;
    public $email;

    public function __construct($verificationCode, $email)
    {
        $this->verificationCode = $verificationCode;
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
            ->view('emails.reset_password')
            ->with([
                'verificationCode' => $this->verificationCode,
                'email' => $this->email,
            ]);
    }
}
