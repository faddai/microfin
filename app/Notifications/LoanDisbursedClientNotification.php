<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 25/02/2017
 * Time: 09:38
 */

namespace App\Notifications;

use App\Entities\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class LoanDisbursedClientNotification extends Notification implements ShouldQueue
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
            <th>Loan Amt. ({$currency})</th>
            <th>Tenure</th>
            <th>Repayment</th>
            <th>Rate<br>(mo)</th>
            <th>Repayment Amt.</th>
            <th>Exp. interest ({$currency})</th>
        </tr>
    </thead>

    <tbody>
        <tr style="border-bottom:1px solid #f5f5f5">
            <td>{$this->loan->getPrincipalAmount()}</td>
            <td>{$this->loan->tenure->label}</td>
            <td>{$this->loan->repaymentPlan->label}</td>
            <td>{$this->loan->rate}%</td>
            <td>{$this->loan->getRepaymentAmount()}</td>
            <td>{$this->loan->getTotalInterest()}</td>
        </tr>
    </tbody>
</table>
TABLE;

        return (new MailMessage)
                    ->greeting(sprintf('Dear %s,', $notifiable->getFullName()))
                    ->subject('Your loan has been disbursed')
                    ->line(sprintf('Your loan amount of %s has been disbursed.', $loanAmount))
                    ->line('Please contact our office for payment of said amount.')
                    ->line('See below for the loan summary;')
                    ->line($loanSummary)
                    ->line('Thank you for your business');
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
