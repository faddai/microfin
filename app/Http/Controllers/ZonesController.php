<?php

namespace App\Http\Controllers;

use App\Entities\Zone;
use App\Http\Requests\AddZoneFormRequest;
use App\Jobs\AddZoneJob;
use Illuminate\Http\Request;

class ZonesController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->dispatch(new AddZoneJob($request));
        } catch (\Exception $e) {
            logger()->error('Zone could not be added', ['error' => $e->getMessage()]);

            flash()->error('The zone could not be added');
        }

        flash()->success('The zone was successfully added');

        return redirect(route_with_hash('settings.index', '#zones'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AddZoneFormRequest|Request $request
     * @param Zone $zone
     * @return \Illuminate\Http\Response
     */
    public function update(AddZoneFormRequest $request, Zone $zone)
    {
        try {
            $this->dispatch(new AddZoneJob($request, $zone));
        } catch (\Exception $e) {
            logger()->error('Zone could not be updated', ['error' => $e->getMessage()]);

            flash()->error('The zone could not be updated');
        }

        flash()->success('The zone was successfully updated');

        return redirect(route_with_hash('settings.index', '#zones'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Zone $zone
     * @return \Illuminate\Http\Response
     */
    public function destroy(Zone $zone)
    {
        try {
            $zone->delete() && cache()->forget('zones');
        } catch (\Exception $e) {
            logger()->error('Zone could not be deleted', ['error' => $e->getMessage()]);

            flash()->error('The zone could not be deleted');
        }

        flash()->success('The zone has been deleted');

        return redirect(route_with_hash('settings.index', '#zones'));
    }
}
