<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 31/12/2016
 * Time: 12:48 AM
 */

namespace App\Traits;

use App\Entities\Loan;


trait GetLoansTrait
{
    /**
     * @param array $where
     * @param bool $isRunningLoan
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \InvalidArgumentException
     */
    protected function loans(array $where = [], $isRunningLoan = true)
    {
        $query = new Loan;

        if (! $isRunningLoan) {
            $query = Loan::withoutGlobalScope('running');
        }

        $query = $query->with(['tenure', 'fees', 'client.clientable', 'createdBy', 'schedule']);

        return $query->when(count($where) !== 0, function ($query) use ($where) {
                return $query->where($where[0], $where[1]);
            })
            ->paginate($this->request->get('limit', 20));
    }
}