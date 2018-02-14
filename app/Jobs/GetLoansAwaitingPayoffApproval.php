<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/06/2017
 * Time: 10:34
 */

namespace App\Jobs;


use App\Entities\LoanPayoff;
use Illuminate\Http\Request;

class GetLoansAwaitingPayoffApproval
{
    /**
     * @var Request
     */
    private $request;

    /**
     * GetLoansAwaitingPayoffApproval constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function handle()
    {
        return LoanPayoff::with(['loan.client.clientable', 'createdBy', 'loan.schedule'])
            ->whereStatus(LoanPayoff::PENDING)
            ->paginate($this->request->get('limit', 30));
    }
}