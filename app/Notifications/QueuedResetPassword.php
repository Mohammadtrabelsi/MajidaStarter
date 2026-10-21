<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * The framework's password-reset notification, queued so the forgot-password
 * request never blocks on the mail transport.
 */
class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use InteractsWithQueue;
}
