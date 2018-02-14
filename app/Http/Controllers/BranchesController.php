<?php

namespace App\Http\Controllers;

use App\Entities\Branch;
use App\Http\Requests\AddBranchFormRequest;
use App\Jobs\AddBranchJob;
use Illuminate\Http\Request;

class BranchesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param AddBranchFormRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddBranchFormRequest $request)
    {
        try {
            $this->dispatchNow(new AddBranchJob($request));
        } catch (\Exception $e) {
            logger()->error('Branch could not be created', ['error' => $e->getMessage()]);

            flash()->error('Branch could not be created');

            return back()->withInput();
        }

        flash()->success('The Branch has been added');

        return redirect(route_with_hash('settings.index', '#branches'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AddBranchFormRequest $request
     * @param Branch $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy(AddBranchFormRequest $request, Branch $branch)
    {
        try {
            $this->dispatchNow(new AddBranchJob($request, $branch));
        } catch (\Exception $e) {
            logger()->error('Branch could not be updated', ['error' => $e->getMessage()]);

            flash()->error('Branch could not be updated');

            return back()->withInput();
        }

        flash()->success('The Branch has been updated');

        return redirect(route_with_hash('settings.index', '#branches'));
    }
}
