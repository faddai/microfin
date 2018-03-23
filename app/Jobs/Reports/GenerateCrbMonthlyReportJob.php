<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Jobs\ExportDataToCsvJob;
use App\Traits\DecoratesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateCrbMonthlyReportJob implements ReportsInterface
{
    use DecoratesReport;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Collection
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

        $this->normalizeAndSetDate();
    }

    /**
     * Execute the job.
     *
     * @return \Illuminate\Support\Collection
     */
    public function handle(): Collection
    {
        // go through running loans
        // get client data
        // categorize based on the type of client (Individual, Corporate)
        // build report for each category using their respective headings
        if($this->request->has('client_type')) {
            Loan::crb($this->request)
                ->each(function (Loan $loan) {

                    $this->report->push($this->getReportDataForLoan($loan));

                }, 100);

            if ($this->request->has('export')) {

                return $this->downloadReportAsCsv();
            }
        }

        $this->setReportTitleAndDescription();

        return $this->report;
    }

    /**
     * Returns the title of this report
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'CRB Report - '. $this->request->get('date')->format('F, Y');
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'CRB Monthly Report for '. $this->request->get('date')->format('F, Y');
    }

    /**
     * Returns the heading used to display report data in HTML table
     * or exported file formats (CSV, Excel, PDF)
     *
     * @return array
     */
    public function getHeader(): array
    {
        return [];
    }

    /**
     * @param Loan $loan
     * @return mixed
     */
    private function getRepaymentsCollectedForTheMonth(Loan $loan)
    {
        return $loan->repaymentCollections()
            ->whereMonth('collected_at', $this->request->get('date')->format('m'))
            ->sum('loan_repayment_collections.amount');
    }

    /**
     * @param Loan $loan
     * @return mixed|string
     */
    private function getDisbursedAmount(Loan $loan)
    {
        return $loan->getPrincipalAmount(false) - $loan->getUpfrontFees(false);
    }

    /**
     * @param Loan $loan
     * @return mixed
     */
    private function getInstallmentsInArrears(Loan $loan)
    {
        return $loan->schedule->filter(function (LoanRepayment $repayment) {
            return $repayment->isDefaulted();
        })->count();
    }

    /**
     * @param $loan
     * @return mixed
     */
    private function getAmountPastDue($loan)
    {
        return $loan->schedule->sum(function (LoanRepayment $repayment) {
            return $repayment->getOutstandingRepaymentAmount(false);
        });

    }

    private function getDaysInArrears(Loan $loan)
    {
        $dueDate = $loan->schedule
            ->filter(function (LoanRepayment $repayment) { return $repayment->isDefaulted(); })
            ->first()
            ->due_date ?? null;

        return $dueDate ? $dueDate->diffInDays($this->request->get('date')) : 0;
    }

    /**
     * @param Loan $loan
     * @return array
     */
    private function getReportDataForLoan(Loan $loan): array
    {
        $client = $loan->client;
        $clientable = $client->clientable;
        $dateFormat = 'dmY';

        $individual = [
            'Surname' => $clientable->lastname,
            'Forename 1' => $clientable->firstname,
            'Forename or Initial 2' => $clientable->middlename,
            'Forename or Initial 3' => '',
            'NRC Number' => $client->identification_type === 'nrc_number' ? $client->identification_number : '',
            'Passport No' => $client->identification_type === 'passport' ? $client->identification_number : '',
            'Nationality' => $client->country->nationality,
            'Driving License No' => $client->identification_type === 'driver_license' ? $client->identification_number : '',
            'Social Security Number' => $client->identification_type === 'social_security_number' ? $client->identification_number : '',
            'Health Insurance Number / NAPSA Number' => $client->identification_type === 'health_insurance_number' ? $client->identification_number : '',
            'Marital Status' => $clientable->marital_status ? strtoupper($clientable->marital_status[0]) : '',
            'No of Dependants' => '',
            'Gender / Sex' => $clientable->gender ? strtoupper($clientable->gender[0]) : '',
            'Date of Birth' => $clientable->dob ? $clientable->dob->format($dateFormat) : '',
            'Place Of Birth' => '',
            'Postal Number' => '',
            'Residence Type / House Type' => '',
            'Duration at this Address (Years)' => '',
            'Duration at this address (Months)' => '',
            'Work Telephone' => $client->phone2,
            'Home Telephone' => '',
            'Mobile Telephone' => $client->phone,
            'Employer Address Line 1' => '',
            'Employer Town' => '',
            'Employer Country' => '',
            'Occupation/ Designation' => '',
            'Employment Duration (Years)' => '',
            'Employment Duration (Months)' => '',
            'Employer Name' => '',
            'Income' => '',
            'Income Frequency' => 'M',
            'Group Name' => $loan->product->name,
            'Group Number' => $loan->product->code,
        ];

        $corporate = [
            'Institution / Company Name' => $client->name,
            'Trading Name' => $client->name,
            'VAT No' => $clientable->vat_number,
            'Company Reg No' => $clientable->business_registration_number,
            'Company Registration Date' => $clientable->date_of_incorporation ? $clientable->date_of_incorporation->format($dateFormat) : '',
            'Company Cease Date' => '',
            'Industry' => $clientable->nature_of_business,
            'Type of Company' => $clientable->company_ownership_type,
            'Company Status' => '',
            'Postal  Number' => '',
            'Location' => '',
            'Telephone 1' => $client->phone1,
            'Telephone 2' => $client->phone2,
            'Telephone 3' => '',
        ];

        $data = $client->isIndividual() ? $individual : $corporate;

        return array_merge($data, [
            'Tax No / TPIN No' => $client->identification_type === 'tax' ? $client->identification_number : '',
            'Postal Code' => '',
            'Town' => '',
            'Country' => $client->country->name,
            'Email Address' => $client->email,
            'Province' => '',
            'District' => '',
            'Plot Number' => '',
            'Physical Address Line 1' => $client->address,
            'Physical Address Line 2' => '',
            'Facsimile / Fax' => '',
            'Delinquency Date' => '',
            'Account Number' => $loan->number,
            'Old Account Number' => '',
            'Account Type' => $loan->client->isIndividual() ? 'I' : 'C',
            'Account Status' => $loan->status === Loan::DISBURSED ? 'Active' : 'Closed',
            'Account Status Change Date' => $loan->updated_at->format($dateFormat),
            'BOZ Classification' => '',
            'Overdraft Type' => '',
            'Grace Period' => $loan->grace_period,
            'Account Owner' => '',
            'Number of Joint Loan Participants' => '',
            'Reporting Currency' => config('app.currency'),
            'Date Account Opened' => $loan->created_at->format($dateFormat),
            'Date Account Updated' => $loan->updated_at->format($dateFormat),
            'Terms Duration / Payment Terms' => $loan->tenure->number_of_months,
            'Account Repayment Term' => $loan->repaymentPlan->label === RepaymentPlan::MONTHLY ? 'MTH' : $loan->repaymentPlan->label,
            'Opening Balance / Credit Limit / Principal' => $loan->getPrincipalAmount(),
            'Amount paid to date' => $loan->getAmountPaid(),
            'Current Balance' => number_format($loan->getBalance(false) * -1, 2),
            'Available Credit' => '',
            'Scheduled Payment Amount' => $loan->getRepaymentAmount(),
            'Actual Payment Amount' => number_format($this->getRepaymentsCollectedForTheMonth($loan), 2),
            'Amount Past Due' => number_format($this->getAmountPastDue($loan)),
            'Installment(s) in Arrears' => $this->getInstallmentsInArrears($loan),
            'Days in Arrears' => $this->getDaysInArrears($loan),
            'Date Closed' => '',
            'Closure Reason' => '',
            // @todo get payment information from $loan->repaymentCollections since that contains actual payments
            'Last Payment Date' => $loan->payments->last() && $loan->payments->last()->repayment_timestamp ? $loan->payments->last()->repayment_timestamp->format($dateFormat) : '',
            'Last Payment Amount' => $loan->payments->last() && $loan->payments->last()->repayment_timestamp ? $loan->payments->last()->getAmount() : '',
            'Interest Rate at Disbursement' => $loan->rate * 12,
            'First Payment Date' => $loan->payments->first() && $loan->payments->first()->repayment_timestamp ? $loan->payments->first()->repayment_timestamp->format($dateFormat) : '',
            'Approved Amount' => $loan->getPrincipalAmount(),
            'Disbursed Amount' => number_format($this->getDisbursedAmount($loan)),
            'Approval Date' => $loan->approved_at->format($dateFormat),
            'Maturity Date' => $loan->maturity_date->format($dateFormat),
            'Interest Type' => 'A',
            'Interest Calculation Method' => $loan->interest_calculation_strategy === Loan::REDUCING_BALANCE_STRATEGY ? 'A' : 'B',
            'Credit Amortization Type' => 'A',
        ]);
    }

    /**
     * @return mixed
     */
    private function downloadReportAsCsv(): mixed
    {
        $filename = sprintf('CRB-%s-%s',
            str_replace('Morph', '', $this->request->get('client_type')),
            $this->request->get('date')->format('M-Y')
        );

        return $this->dispatch(new ExportDataToCsvJob($this->report, $filename));
    }

}
