<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued version of the framework's password reset notification so the email
 * is sent by a queue worker instead of blocking the HTTP request.
 */
class QueuedResetPassword extends BaseResetPassword implements ShouldQueue
{
    use Queueable;
}
