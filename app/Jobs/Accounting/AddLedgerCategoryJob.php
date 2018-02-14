<?php

namespace App\Jobs\Accounting;

use App\Entities\Accounting\LedgerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AddLedgerCategoryJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var LedgerCategory
     */
    private $category;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LedgerCategory $category
     */
    public function __construct(Request $request, LedgerCategory $category = null)
    {
        $this->request = $request;
        $this->category = $category ?? new LedgerCategory;
    }

    /**
     * Execute the job.
     *
     * @return LedgerCategory
     */
    public function handle()
    {
        return DB::transaction(function () {
            return $this->addOrUpdateLedgerCategory();
        });
    }

    private function addOrUpdateLedgerCategory()
    {
        foreach ($this->category->getFillable() as $fillable) {
            if ($this->request->has($fillable)) {
                $this->category->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->category->save();

        return $this->category;
    }
}
