<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Lang;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('notifications.verifyEmailSubject'))
            ->greeting(__('notifications.hello'))
            ->line(Lang::get('notifications.clickButton'))
            ->action(Lang::get('notifications.verifyEmailAddress'), $url)
            ->line(__('notifications.ifNotYourRequestVerifyEmail'))
            ->salutation(null);
    }
}
