<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendResetClientePasswordNotification extends Notification
{
    use Queueable;
    private $password;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($password)
    {
        $this->password = $password;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $app = env("APP_NAME");
        return (new MailMessage)
            ->markdown('mails.credentials..send_reset_cliente_password')
            ->subject('Redifinição da Senha')
            ->greeting("Saudações $notifiable->nome!")
            ->line("Redefiniu a sua senha no $app.")
            ->line("Use a senha a baixo para aceder a sua conta no $app.")
            ->line("Senha: $this->password")
            ->line('Obrigado por usar a aplicação! || Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
