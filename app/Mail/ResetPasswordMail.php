<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ResetPasswordMail extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;
    public $url;
    protected $resetCode;

    public function __construct($token, $url)
    {
        $this->token = $token;
        $this->url   = $url;
        $this->resetCode = Str::random(6);
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Store the reset code in cache
        $cacheKey = 'password_reset_code_' . $notifiable->email;
        Cache::put($cacheKey, $this->resetCode, now()->addMinutes(30));

        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You requested a password reset.')
            ->line('**Method 1: Click the reset link below**')
            ->action('Reset Password', $this->url)
            ->line('**Method 2: Use this reset code**')
            ->line('Your reset code is: **' . $this->resetCode . '**')
            ->line('Enter this code in the password reset form on our website.')
            ->line('This code will expire in 30 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
