<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Client;
use App\Traits\DecoratesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateCustomerDataReportJob implements ReportsInterface
{
    use DecoratesReport;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $report;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->report = collect();
    }

    /**
     * Returns the title of this report
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Customer Data Report';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        $clientable = $this->request->get('client_type');

        return $clientable ?
            sprintf('Showing All %s Clients', str_replace('Morph', '', $clientable)) : 'Showing All Clients';
    }

    /**
     * Returns the heading used to display report data in HTML table
     * or exported file formats (CSV, Excel, PDF)
     *
     * @return array
     */
    public function getHeader(): array
    {
        return [
            'Name',
            'Address',
            'Tel Number',
            'ID Type',
            'ID Number',
            'Email Address',
            'Date of Birth',
            'Gender',
        ];
    }

    /**
     * Add logic to retrieve data for this report
     *
     * @return Collection
     */
    public function handle(): Collection
    {
        Client::with('clientable')
            ->when($this->request->get('client_type'), function ($query) {
                return $query->where('clientable_type', $this->request->get('client_type'));
            })
            ->get()
            ->each(function (Client $client) {
                $this->report->push(collect([
                    'id' => $client->id,
                    'name' => $client->getFullName(),
                    'address' => $client->address,
                    'phone' => $client->phone1,
                    'identification_type' => $client->identification_type,
                    'identification_number' => $client->identification_number,
                    'email' => $client->email,
                    'dob' => $client->clientable->dob,
                    'gender' => $client->clientable->gender,
                ]));
            });

        $this->setReportTitleAndDescription();

        $this->prependHeaderToReport();

        return $this->report;
    }

    /**
     * @return Collection
     */
    public function downloadAsCsv()
    {
        $report = $this->handle();

        $report->shift();

        $_report = $report->map(function (Collection $collection) {

            return [
                'Name' => $collection->get('name'),
                'Address' => $collection->get('address'),
                'Tel Number' => $collection->get('phone'),
                'ID Type' => trans('identification_types')[$collection->get('identification_type')],
                'ID Number' => $collection->get('identification_number'),
                'Email Address' => $collection->get('email'),
                'Date of Birth' => $collection->get('dob') ? $collection->get('dob')->format('d/m/Y') : '',
                'Gender' => ucfirst($collection->get('gender')),
            ];
        });

        $_report->meta = collect([
            $this->getTitle() => '',
            $this->getDescription() => '',
        ]);

        return $_report;
    }

}
