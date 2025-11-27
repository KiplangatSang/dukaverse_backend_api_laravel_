<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VerifyEmail extends Notification
{
    use Queueable;

    protected $verificationCode;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->verificationCode = Str::random(6);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Store the verification code in cache
        $cacheKey = 'email_verification_code_' . $notifiable->id;
        Cache::put($cacheKey, $this->verificationCode, now()->addMinutes(30));

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Please verify your email address using one of the following methods:')
            ->line('**Method 1: Click the verification link below**')
            ->action('Verify Email', $this->verificationUrl($notifiable))
            ->line('**Method 2: Use this verification code**')
            ->line('Your verification code is: **' . $this->verificationCode . '**')
            ->line('Enter this code in the verification form on our website.')
            ->line('This code will expire in 30 minutes.')
            ->line('If you did not create an account, no further action is required.');
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return config('app.frontend_url') . '/email/verify/' . $notifiable->getKey() . '/' . sha1($notifiable->getEmailForVerification());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
