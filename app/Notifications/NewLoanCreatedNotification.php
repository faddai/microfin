<?php

namespace App\Notifications;

use App\Entities\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class NewLoanCreatedNotification extends Notification implements ShouldQueue
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
        $currency = config('app.currency');

        $loanAmount = $currency. ' '. $this->loan->getPrincipalAmount();

        $loanSummary = <<<TABLE
<table border="0" cellpadding="12" width="100%" style="border-collapse:collapse;margin:0;padding:0;font-family:Arial;">
    <thead style="border-bottom:2px solid #e7e7e7">
        <tr align="left" style="color:#aeb1b8">
            <th>Client</th>
            <th>Loan ({$currency})</th>
            <th>Tenure</th>
            <th>Repayment</th>
            <th>Rate (% mo)</th>
            <th>Exp. interest ({$currency})</th>
        </tr>
    </thead>

    <tbody>
        <tr style="border-bottom:1px solid #f5f5f5">
            <td>{$this->loan->client->getFullName()}</td>
            <td>{$this->loan->getPrincipalAmount()}</td>
            <td>{$this->loan->tenure->label}</td>
            <td>{$this->loan->repaymentPlan->label}</td>
            <td>{$this->loan->rate}</td>
            <td>{$this->loan->getTotalInterest()}</td>
        </tr>
    </tbody>
</table>
TABLE;

        return (new MailMessage)
            ->subject("A loan of {$loanAmount} needs your approval")
            ->greeting("Hi {$notifiable->getFullName()},")
            ->line("{$this->loan->createdBy->getFullName()} is requesting for your approval of a loan. See loan summary below;")
            ->line($loanSummary)
            ->action('View loan details and approve', route('loans.show', ['loan' => $this->loan]));
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
