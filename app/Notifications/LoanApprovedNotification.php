<?php

namespace App\Notifications;

use App\Entities\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class LoanApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Loan
     */
    public $loan;

    /**
     * Create a new notification instance.
     *
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
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
        $loanAmount = config('app.currency'). ' '. $this->loan->getPrincipalAmount();

        return (new MailMessage)
                    ->subject("A loan of {$loanAmount} has been approved")
                    ->greeting("Hi {$notifiable->getFullName()},")
                    ->line("{$this->loan->approvedBy->getFullName()} has approved a loan amount of {$loanAmount}.")
                    ->line('Please indicate your acceptance by disbursing the approved amount to make it available to the Client.')
                    ->action('View loan details', route('loans.show', ['loan' => $this->loan]));
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
