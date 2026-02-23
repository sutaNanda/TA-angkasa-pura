<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TechnicianCredentialsNotification extends Notification
{
    use Queueable;

    public $password;

    /**
     * Create a new notification instance.
     */
    public function __construct($password)
    {
        $this->password = $password;
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
        return (new MailMessage)
                    ->subject('Selamat Bergabung - Akun Teknisi Asset Monitoring')
                    ->greeting('Halo, ' . $notifiable->name)
                    ->line('Akun Anda telah dibuat sebagai Teknisi di sistem Asset Monitoring.')
                    ->line('Berikut adalah kredensial login Anda:')
                    ->line('Email: ' . $notifiable->email)
                    ->line('Password Sementara: ' . $this->password)
                    ->action('Login Sekarang', route('login'))
                    ->line('Anda diwajibkan mengganti password ini saat login pertama kali.')
                    ->line('Terima kasih!');
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
