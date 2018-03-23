<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 25/04/2017
 * Time: 19:49
 */

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

trait DecoratesReport
{

    use DispatchesJobs;

    /**
     * @return void
     */
    public function setReportTitleAndDescription()
    {
        $this->report->title = $this->getTitle();
        $this->report->description = $this->getDescription();
    }

    /**
     * @return void
     */
    public function prependHeaderToReport()
    {
        $this->report->prepend($this->getHeader());
    }

    /**
     * Normalize the collection dates sent in the request
     *
     * @return void
     */
    private function setStartAndEndDates()
    {
        $startDate = $this->request->has('startDate') ?
            Carbon::parse($this->request->get('startDate')) : Carbon::today()->startOfMonth();

        $endDate = $this->request->has('endDate') ?
            Carbon::parse($this->request->get('endDate')) : Carbon::today()->endOfMonth();


        $this->request->merge(compact('startDate', 'endDate'));
    }

    /**
     * Normalize the collection date sent in the request
     **/
    private function normalizeAndSetDate()
    {
        try {

            $date = Carbon::today();

            if ($this->request->has('date') && $this->request->get('date') !== '') {
                $date = Carbon::createFromFormat('d/m/Y', $this->request->get('date'));
            }

            $this->request->merge(compact('date'));

        } catch (\InvalidArgumentException $exception) {
            logger()->error('Collection date couldn\'t be formatted');
        }
    }
}