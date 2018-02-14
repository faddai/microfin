<?php

namespace App\Events;

use App\Entities\ClientTransaction;
use Illuminate\Queue\SerializesModels;


class DepositAddedEvent
{
    use SerializesModels;

    /**
     * @var ClientTransaction
     */
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param ClientTransaction $transaction
     */
    public function __construct(ClientTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
