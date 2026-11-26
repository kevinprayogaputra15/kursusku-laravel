<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email reset password berbahasa Indonesia.
 * Mewarisi ResetPassword bawaan Laravel, sehingga pembuatan token dan URL
 * (route 'password.reset') tetap memakai mekanisme bawaan.
 */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $broker = config('auth.defaults.passwords');
        $minutes = config("auth.passwords.{$broker}.expire");

        return (new MailMessage)
            ->subject('Reset Password KursusKu')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akun KursusKu Anda.')
            ->action('Reset Password', $url)
            ->line("Tautan ini berlaku selama {$minutes} menit dan hanya dapat dipakai satu kali.")
            ->line('Jika Anda tidak merasa meminta reset password, abaikan email ini.')
            ->salutation('Salam, KursusKu');
    }
}
