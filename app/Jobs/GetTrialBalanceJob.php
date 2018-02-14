<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 00:46
 */

namespace App\Jobs;


use App\Entities\Accounting\Ledger;
use Illuminate\Http\Request;

class GetTrialBalanceJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * GetTrialBalanceJob constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function handle()
    {
        return Ledger::with('entries', 'category')
            ->orderBy('code')
            ->get();
    }
}