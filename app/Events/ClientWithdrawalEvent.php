<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/02/2017
 * Time: 09:16
 */

namespace App\Events;


use App\Entities\ClientTransaction;

class ClientWithdrawalEvent
{
    /**
     * @var ClientTransaction
     */
    public $transaction;

    /**
     * ClientWithdrawalEvent constructor.
     * @param ClientTransaction $clientTransaction
     */
    public function __construct(ClientTransaction $clientTransaction)
    {
        $this->transaction = $clientTransaction;
    }
}