<?php

namespace App\Jobs;

use App\Entities\Zone;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;

class AddZoneJob
{
    use Queueable;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Zone
     */
    private $zone;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Zone $zone
     */
    public function __construct(Request $request, Zone $zone = null)
    {
        $this->request = $request;
        $this->zone = $zone;
    }

    /**
     * Execute the job.
     *
     * @return Zone
     */
    public function handle()
    {
        if (! is_null($this->zone)) {
            $this->zone->update(['name' => $this->request->get('name')]);

            $loan = $this->zone;
        } else {
            $loan = Zone::create(['name' => $this->request->get('name')]);
        }

        cache()->forget('zones');

        return $loan;
    }
}
