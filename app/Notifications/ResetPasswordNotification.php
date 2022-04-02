<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $url = url(config(config('inpriparo.frontend') . '.protocol') . '://' . app('currentTenant')->domain . config(config('inpriparo.frontend') . '.reset_password_redirect_route') . '/' . $this->token);

        return $this->buildMailMessage($url);
    }

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('notifications.resetPasswordSubject'))
            ->greeting(__('notifications.hello'))
            ->line(__('notifications.youAreReceivingThisEmail'))
            ->action(__('notifications.resetPassword'), $url)
            ->line(__('notifications.expireIn', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(__('notifications.ifNotYourRequestPasswordReset'))
            ->salutation(null);
    }
}
