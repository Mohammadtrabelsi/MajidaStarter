<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\InteractsWithQueue;

/**
 * The framework's email-verification notification, but queued so registration
 * and "resend verification" requests never block on the mail transport.
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Localize the mail subject/greeting through the app's translation layer.
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $url)
            ->line(__('If you did not create an account, no further action is required.'));
    }
}
