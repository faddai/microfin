<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/02/2017
 * Time: 22:02
 */

namespace App\Exceptions;


use Exception;

class UnbalancedLedgerEntryException extends LedgerEntryException
{
    protected $message = 'Entries submitted for the Transaction does not balance. ';

    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        $this->message .= $message;

        parent::__construct($this->message, $code, $previous);
    }
}