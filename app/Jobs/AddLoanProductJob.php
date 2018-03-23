<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 26/02/2017
 * Time: 16:33
 */

namespace App\Jobs;

use App\Entities\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddLoanProductJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var LoanProduct
     */
    private $product;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanProduct $product
     */
    public function __construct(Request $request, LoanProduct $product = null)
    {
        $this->request = $request;
        $this->product = $product ?? new LoanProduct;
    }

    /**
     * Execute the job.
     *
     * @return LoanProduct
     */
    public function handle()
    {
        return DB::transaction(function () {
            return $this->saveOrUpdateProduct();
        });
    }

    /**
     * @return LoanProduct
     */
    private function saveOrUpdateProduct()
    {
        foreach ($this->product->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->product->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->product->save();

        cache()->forget('products');

        return $this->product;
    }
}
