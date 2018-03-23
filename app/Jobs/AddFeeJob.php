<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/03/2017
 * Time: 02:06
 */

namespace App\Jobs;

use App\Entities\Fee;
use Illuminate\Http\Request;


class AddFeeJob
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Fee
     */
    private $fee;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Fee $fee
     */
    public function __construct(Request $request, Fee $fee = null)
    {
        $this->request = $request;
        $this->fee = $fee ?? new Fee;
    }

    /**
     * Execute the job.
     *
     * @return Fee
     */
    public function handle()
    {
        // set fee that was previously paid upfront to otherwise
        if ($this->fee->exists && ! $this->request->filled('is_paid_upfront')) {
            $this->fee->is_paid_upfront = 0;
        }

        foreach ($this->fee->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->fee->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->fee->save();

        cache()->forget('fees');

        return $this->fee;
    }
}
