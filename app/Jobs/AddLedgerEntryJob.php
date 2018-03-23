<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/02/2017
 * Time: 09:52
 */

namespace App\Jobs;

use App\Entities\Accounting\LedgerEntry;
use App\Exceptions\LedgerEntryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;


class AddLedgerEntryJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var LedgerEntry
     */
    private $entry;

    /**
     * AddLedgerEntryJob constructor.
     * @param Request $request
     * @param LedgerEntry $entry
     */
    public function __construct(Request $request, LedgerEntry $entry= null)
    {
        $this->request = $request;
        $this->entry = $entry ?? new LedgerEntry(['ledger_transaction_id' => $this->request->get('transaction_id')]);
    }

    /**
     * @return mixed
     * @throws \App\Exceptions\LedgerEntryException
     */
    public function handle()
    {
        logger('Add a Ledger Entry', ['request' => $this->request->all()]);

        return DB::transaction(function () {
            return $this->saveLedgerEntry();
        });
    }

    /**
     * @return LedgerEntry
     * @throws \App\Exceptions\LedgerEntryException
     */
    private function saveLedgerEntry()
    {
        if (! $this->isValidTransaction()) {
            throw new LedgerEntryException('You cannot post an entry without a valid transaction');
        }

        foreach ($this->entry->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->entry->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->entry->save();

        return $this->entry;
    }

    /**
     * @return bool
     */
    private function isValidTransaction()
    {
        return $this->entry->ledger_transaction_id && Uuid::isValid($this->entry->ledger_transaction_id);
    }
}