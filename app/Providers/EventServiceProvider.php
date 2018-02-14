<?php

namespace App\Providers;

use App\Events\DepositAddedEvent;
use App\Events\LoanApprovedEvent;
use App\Events\LoanCreatedEvent;
use App\Events\LoanDeclinedEvent;
use App\Events\LoanDisbursedEvent;
use App\Events\LoanRepaymentDeductedEvent;
use App\Listeners\CreditClientAccountWithDisbursedAmount;
use App\Listeners\DeductLoanRepaymentAfterClientDepositTransaction;
use App\Listeners\NotifyUsersWhoCanApproveLoan;
use App\Listeners\NotifyUsersWhoCanDisburseLoan;
use App\Listeners\PostClientTransactionsToGeneralLedgersSubscriber;
use App\Listeners\PostDeductedLoanRepaymentAmountToClientTransactions;
use App\Listeners\PostDisbursementToGeneralLedger;
use App\Listeners\PostDisbursementToLoanAccountStatement;
use App\Listeners\PostLoanRepaymentDeductionToLoanAccountStatement;
use App\Listeners\PostRepaymentDeductionToGeneralLedger;
use App\Listeners\RecordLoanRepaymentCollection;
use App\Listeners\RecordUserLastSuccessfulLogin;
use App\Listeners\ReverseRestructureWhenLoanIsDeclined;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        Login::class => [
            RecordUserLastSuccessfulLogin::class,
        ],

        /**
         * @see PostClientTransactionsToGeneralLedgersSubscriber for other listeners for this event
         */
        DepositAddedEvent::class => [
            DeductLoanRepaymentAfterClientDepositTransaction::class,
        ],

        LoanCreatedEvent::class => [
            NotifyUsersWhoCanApproveLoan::class,
        ],

        LoanApprovedEvent::class => [
            NotifyUsersWhoCanDisburseLoan::class,
        ],

        LoanDisbursedEvent::class => [
            CreditClientAccountWithDisbursedAmount::class,
            PostDisbursementToGeneralLedger::class,
            PostDisbursementToLoanAccountStatement::class,
        ],

        LoanDeclinedEvent::class => [
            ReverseRestructureWhenLoanIsDeclined::class,
        ],

        LoanRepaymentDeductedEvent::class => [
            PostRepaymentDeductionToGeneralLedger::class,
            PostDeductedLoanRepaymentAmountToClientTransactions::class,
            PostLoanRepaymentDeductionToLoanAccountStatement::class,
            RecordLoanRepaymentCollection::class,
        ],

    ];

    /**
     * @var array
     */
    protected $subscribe = [
        PostClientTransactionsToGeneralLedgersSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
