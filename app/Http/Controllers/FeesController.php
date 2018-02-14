<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/03/2017
 * Time: 02:06
 */

namespace App\Http\Controllers;

use App\Entities\Fee;
use App\Http\Requests\AddFeeFormRequest;
use App\Jobs\AddFeeJob;
use Illuminate\Http\Request;

class FeesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param AddFeeFormRequest|Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(AddFeeFormRequest $request)
    {
        try {
            $this->dispatch(new AddFeeJob($request));
        } catch (\Exception $exception) {
            logger()->error('An error occurred while creating fee', compact('exception'));

            flash()->error('The fee could not be created. Error: '. $exception->getMessage());

            return redirect(route_with_hash('settings.index', '#fees'))->withInput();
        }

        flash()->success('Fee has been created');

        return redirect(route_with_hash('settings.index', '#fees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AddFeeFormRequest $request
     * @param Fee $fee
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function update(AddFeeFormRequest $request, Fee $fee)
    {
        try {
            $this->dispatch(new AddFeeJob($request, $fee));
        } catch (\Exception $exception) {
            logger()->error('An error occurred while updating fee', compact('exception'));

            flash()->error('The fee could not be updated. Error: '. $exception->getMessage());

            return redirect(route_with_hash('settings.index', '#fees'))->withInput();
        }

        flash()->success('Fee has been updated');

        return redirect(route_with_hash('settings.index', '#fees'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Fee $fee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fee $fee)
    {
        try {
            $fee->delete() && cache()->forget('fees');
        } catch (\Exception $e) {
            logger()->error('The fee could not be deleted.', ['error' => $e]);
            flash()->error('Something unexpected happened. The fee could not be deleted.');
        }

        flash()->success('The fee has been deleted');

        return redirect(route_with_hash('settings.index', '#fees'));
    }
}
