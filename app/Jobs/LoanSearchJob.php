<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 21/03/2017
 * Time: 01:43
 */

namespace App\Jobs;


use App\Entities\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoanSearchJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * LoanSearchJob constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function handle()
    {
        $creditOfficer = $this->request->get('credit_officer');
        $product = $this->request->get('product_id');
        $term = $this->request->get('term');
        $status = $this->request->get('status');
        $limit = $this->request->get('limit', config('microfin.limit'));
        $startDate = Carbon::parse($this->request->get('startDate'));
        $endDate = Carbon::parse($this->request->get('endDate'));

        logger('Loan search filters', compact('creditOfficer', 'status', 'term', 'startDate', 'endDate'));

        return Loan::with('client.clientable', 'schedule', 'tenure', 'creditOfficer', 'fees', 'createdBy')
            ->when($status, function ($query) use ($status) {
                return $query->whereStatus($status);
            })
            ->when($status && $startDate && $endDate, function ($query) use ($status, $startDate, $endDate) {

                $status = $status === Loan::PENDING ? 'created_at' : "{$status}_at";

                return $query->whereBetween($status, [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
            })
            ->when($creditOfficer, function ($query) use ($creditOfficer) {
                return $query->whereCreditOfficer($creditOfficer);
            })
            ->when($product, function ($query) use ($product) {
                return $query->whereLoanProductId($product);
            })
            ->when($term, function ($query) use ($term) {
                return $query->where('number', 'like', "%$term%")
                    ->orWhereHas('client', function ($query) use ($term) {
                        return $query->where('account_number', 'like', "%$term%")
                            ->orWhere('name', 'like', "%$term%");
                    });
            })
            ->paginate($limit);
    }
}