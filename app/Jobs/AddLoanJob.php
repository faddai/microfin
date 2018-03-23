<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 1:49 PM
 */

namespace App\Jobs;

use App\Entities\Client;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\User;
use App\Events\LoanCreatedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class AddLoanJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Loan
     */
    private $loan;

    /**
     * @var User
     */
    private $authUser;

    /**
     * @var bool
     */
    private $isANewLoan;
    /**
     * @var bool
     */
    private $sendEmailNotification;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Loan $loan
     * @param bool $sendEmailNotification Purposely put here to help with loan migration
     */
    public function __construct(Request $request, Loan $loan = null, $sendEmailNotification = true)
    {
        $this->request = $request;
        $this->authUser = $this->request->user() ?? null;
        $this->isANewLoan = $loan === null;
        $this->sendEmailNotification = $sendEmailNotification;

        $this->loan = $loan ?? new Loan([
                'status' => Loan::PENDING,
                'user_id' => $this->authUser->id,
                'number' => $request->get('number', $this->generateLoanNumber())
            ]);
    }

    /**
     * Execute the job.
     **/
    public function handle(): Loan
    {
        return DB::transaction(function () {
            // save loan
            $this->addOrUpdateLoan();

            // generate repayment schedule
            dispatch_now(new GenerateLoanRepaymentScheduleJob($this->loan));

            if ($this->sendEmailNotification) {
                $this->isANewLoan && event(new LoanCreatedEvent($this->loan));
            }

            return $this->loan;
        });
    }

    private function addOrUpdateLoan()
    {
        // a client is required for creating loans
        if (! $this->loan->exists && ! $this->request->filled('client_id')) {
            throw new BadRequestHttpException('A client is required to create a loan');
        }

        $client = $this->loan->exists ? $this->loan->client : Client::find($this->request->get('client_id'));

        foreach ($this->loan->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->loan->{$fillable} = $this->request->get($fillable);
            }
        }

        // set age group and loan size
        $this->loan->age_group = $client->isIndividual() ? get_age_group($client->clientable->dob) : 'N/A';

        $this->loan->loan_size = get_loan_size($this->loan->amount);

        $this->loan->save();

        // make sure a guarantor has at least a name before going ahead to create it
        $this->requestHasGuarantor() && $this->saveOrUpdateGuarantors();

        // make sure a collateral has a label
        $this->requestHasCollateral() && $this->saveOrUpdateCollaterals();

        $this->requestHasFees() && $this->saveFees();

        return $this->loan;
    }

    /**
     * @return Collection
     */
    private function saveOrUpdateGuarantors()
    {
        return collect($this->request->get('guarantors', []))->each(function ($guarantor) {

            $guarantorRequestData = new Request($guarantor);

            return dispatch_now(new AddGuarantorJob($guarantorRequestData, $this->loan));
        });
    }

    /**
     * @return Collection
     */
    private function saveOrUpdateCollaterals()
    {
        return collect($this->request->get('collaterals', []))->each(function ($collateral) {

            $collateralRequestData = new Request($collateral);

            return dispatch_now(new AddCollateralJob($collateralRequestData, $this->loan));

        });
    }

    /**
     * @return Loan
     */
    private function saveFees()
    {
        collect($this->request->get('fees'))
            ->reject(function ($fee) {
                return $fee['rate'] <= 0; // we're not interested in 0 rate fees
            })
            ->each(function ($loanFee) {

                $loanFee = collect($loanFee);

                $rate = $loanFee->get('rate');
                $amount = $this->loan->getFeeAmount($rate / 100);

                $is_paid_upfront = $this->feeInRequestIsPaidUpfront($loanFee);

                $fee = Fee::findOrFail($loanFee->get('id'));

                if ($fee->isFixed()) {
                    $amount = $rate;
                    $rate = ($rate / $this->loan->getPrincipalAmount(false)) * 100;
                }

                $this->loan->fees()->save($fee, compact('rate', 'amount', 'is_paid_upfront'));
            });

        return $this->loan;
    }

    /**
     * @return bool
     */
    private function requestHasGuarantor()
    {
        return $this->request->filled('guarantors') &&
            $this->request->filled('guarantors.*.name') &&
            $this->request->get('guarantors')[0]['name'];
    }

    /**
     * @return bool
     */
    private function requestHasCollateral()
    {
        return $this->request->filled('collaterals') &&
            $this->request->filled('collaterals.*.label') &&
            $this->request->get('collaterals')[0]['label'];
    }

    /**
     * @return bool
     */
    private function requestHasFees()
    {
        return $this->request->filled('fees') && $this->request->filled('fees.*.rate');
    }

    /**
     * @param Collection $loanFee
     * @return bool
     */
    private function feeInRequestIsPaidUpfront(Collection $loanFee): bool
    {
        return $loanFee->has('is_paid_upfront') && $loanFee->get('is_paid_upfront');
    }

    private function generateLoanNumber()
    {
        // branch code + 7-digit incremented number
        $branchCode = $this->authUser->branch->code ?? null;
        $lastLoan =  Loan::where('number', 'like', $branchCode.'%')->orderBy('id', 'DESC')->take(1)->first();

        return null === $lastLoan ? sprintf('%s0000001', $branchCode) : sprintf('00%d', $lastLoan->number + 1);
    }
}