<?php

namespace App\Jobs;

use App\Entities\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddBranchJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Branch
     */
    private $branch;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Branch $branch
     */
    public function __construct(Request $request, Branch $branch = null)
    {
        $this->request = $request;
        $this->branch = $branch;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->addOrUpdateBranch();
    }

    /**
     * @return mixed
     */
    private function addOrUpdateBranch()
    {
        return DB::transaction(function () {

            $branch = $this->branch ?: new Branch();

            foreach ($branch->getFillable() as $fillable) {
                if ($this->request->has($fillable)) {
                    $branch->{$fillable} = $this->request->get($fillable);
                }
            }

            $branch->save();

            cache()->forget('branches');

            return $branch;
        });
    }

}
