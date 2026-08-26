<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

/*
|--------------------------------------------------------------------------
| ResetPasswordApi
|--------------------------------------------------------------------------
| Extends Laravel's built-in reset email but changes the link inside it.
|
| Default behavior expects a web page route named "password.reset",
| which does not exist in an API-only project.
|
| Instead we build a link pointing to the FRONTEND page:
|   {FRONTEND_URL}/reset-password?token=xxx&email=user@example.com
| The frontend then sends token+email+new password to POST /api/reset-password.
*/

class ResetPasswordApi extends ResetPassword
{
    protected function resetUrl($notifiable): string
    {
        return rtrim(config('app.frontend_url'), '/') . '/reset-password'
            . '?token=' . $this->token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
    }
}
