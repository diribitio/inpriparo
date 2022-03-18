<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class AdminInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $token = Password::broker()->createToken($notifiable);
        $resetUrl = url(config(config('inpriparo.frontend') . '.protocol') . '://' . app('currentTenant')->domain . config(config('inpriparo.frontend') . '.reset_password_redirect_route') . '/' . $token);

        $app = config('app.name');

        return (new MailMessage())
            ->subject("$app Invitation")
            ->greeting("Hello $notifiable->name,")
            ->line("You have been invited to use $app!")
            ->line('To get started you need to set a password.')
            ->action('Set password', $resetUrl);
    }
}
