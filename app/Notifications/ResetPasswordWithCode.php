<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordWithCode extends ResetPassword
{
    public $token;
    public $verificationCode;

    public function __construct($token)
    {
        $this->token = $token;
        $this->verificationCode = rand(100000, 999999); // 6-digit code
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password')
            ->line('Use the below verification code to reset your password:')
            ->line('**Verification Code: ' . $this->verificationCode . '**')
            ->action('Reset Password', url('auth/resetPassword' . $this->token . '?email=' . $notifiable->email))
            ->line('If you did not request this, please ignore this email.');
    }
}
