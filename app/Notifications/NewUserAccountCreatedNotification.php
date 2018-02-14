<?php

namespace App\Notifications;

use App\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class NewUserAccountCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $token = Password::broker()->getRepository()->create($this->user);
        $expire = config('auth.passwords.users.expire') / 60;
        $url = route('password.reset', ['token' => $token, 'email' => $this->user->email]);

        return (new MailMessage)
                    ->subject(trans('users.invitation_mail_subject'))
                    ->greeting(sprintf('Hi %s,', $this->user->getFullName()))
                    ->line(sprintf("You have been invited to use %s at %s. To complete your account setup,
                    click the button below to set a new password for yourself.", config('app.name'), config('app.company')))
                    ->line("You will use your email address and the password you set to access your account.")
                    ->action('Reset your password', $url)
                    ->line(sprintf("NB: This password reset link expires in %d %s.", $expire, str_plural('hour', $expire)))
                    ->line('Thank you for using our application!');
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
