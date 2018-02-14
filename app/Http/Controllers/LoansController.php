<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 1:49 PM
 */

namespace App\Http\Controllers;

use App\Entities\BusinessSector;
use App\Entities\Client;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\LoanType;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Entities\User;
use App\Entities\Zone;
use App\Http\Requests;
use App\Jobs\AddLoanJob;
use App\Jobs\Exports\GetDataForLoanScheduleExport;
use App\Jobs\Exports\GetDataForLoanStatementExport;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use App\Jobs\GetPendingLoansJob;
use App\Jobs\LoanSearchJob;
use App\Jobs\RestructureLoanJob;
use Illuminate\Http\Request;


class LoansController extends Controller
{
    public function index(Request $request)
    {
        $request->merge(['status' => 'pending']);

        $loans = $this->dispatch(new LoanSearchJob($request));

        return view('dashboard.loans.index', compact('loans'));
    }

    public function create(Request $request, $loan = null)
    {
        $loan = Loan::firstOrNew(['id' => $loan]);
        $loan->amount = $loan->getBalance(false);
        $title = $loan->exists ? 'Restructure Loan #'. $loan->number : 'New Loan Application';
        $clients = Client::with(['clientable'])->get();
        $tenures = Tenure::all(['id', 'label']);
        $repaymentPlans = RepaymentPlan::orderBy('number_of_days', 'DESC')->get();
        $creditOfficers = User::creditOfficers();
        $loanTypes = LoanType::all();
        $zones = Zone::all(['id', 'name']);
        $sectors = BusinessSector::all(['id', 'name']);
        $interestCalculationStrategies = collect([
            Loan::STRAIGHT_LINE_STRATEGY,
            Loan::REDUCING_BALANCE_STRATEGY
        ])->flatMap(function ($strategy) {
            return [$strategy => ucwords(str_replace('_', ' ', $strategy))];
        });
        $fees = cache()->rememberForever('fees', function () {
            return Fee::all();
        });
        $products = cache('products');

        return view('dashboard.loans.create', compact('loan', 'clients', 'tenures', 'repaymentPlans', 'creditOfficers',
            'loanTypes', 'zones', 'sectors', 'interestCalculationStrategies', 'fees', 'products', 'title'));
    }

    public function store(Requests\AddLoanFormRequest $request)
    {
        try {
            $this->dispatch(new AddLoanJob($request));
        } catch (\Exception $exception) {
            logger()->error('Loan could not be created', compact('exception'));

            flash()->error('Loan could not be created');

            return redirect()->back()->withInput();
        }

        flash()->success('The loan has been created pending approval');

        return redirect()->route('loans.index');
    }

    public function edit(Loan $loan)
    {
        $loan = $loan->load(['client', 'guarantors', 'collaterals', 'type', 'zone', 'sector']);

        $clients = Client::with(['clientable'])->get();
        $tenures = Tenure::all(['id', 'label']);
        $repaymentPlans = RepaymentPlan::all(['id', 'label']);
        $creditOfficers = User::creditOfficers();
        $loanTypes = LoanType::all();
        $zones = Zone::all(['id', 'name']);
        $sectors = BusinessSector::all(['id', 'name']);

        return view('dashboard.loans.create', compact('loan', 'clients', 'tenures', 'repaymentPlans', 'creditOfficers',
            'loanTypes', 'zones', 'sectors'));

    }

    public function update(Requests\AddLoanFormRequest $request, Loan $loan)
    {
        try {

            if ($request->has('restructure')) {
                $message = 'Restructure successful. A new Loan has been created pending approval.';
                $this->dispatch(new RestructureLoanJob($request, $loan));
            } else {
                $message = 'The loan been updated';
                $this->dispatch(new AddLoanJob($request, $loan));
            }

            flash()->success($message);

            return redirect()->route('loans.index');

        } catch (\Exception $exception) {
            logger()->error('Loan could not be updated', compact('exception'));

            flash()->error('Loan could not be updated: '. $exception->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function show(Loan $loan)
    {
        $loan->load('statement.entries', 'schedule.loan');
        
        return view('dashboard.loans.show', compact('loan'));
    }

    public function regenerateRepaymentSchedule(Loan $loan)
    {
        try {
            $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan, true));
        } catch (\Exception $exception) {
            logger()->error('An error occurred while regenerating repayment schedule', compact('exception'));
        }

        flash()->success('Schedule has been regenerated');

        return back();
    }

    /**
     * @todo Validate submitted filters most especially, status
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $loans = $this->dispatch(new LoanSearchJob($request));

        $view = $request->get('status') === Loan::PENDING ? 'index' : $request->get('status');

        return view("dashboard.loans.{$view}", compact('loans'));
    }

    /**
     * @param Request $request
     * @param Loan $loan
     * @return mixed
     */
    public function downloadSchedule(Request $request, Loan $loan)
    {
        $options = [
            'filename' => 'loan-schedule-'. $loan->number,
            'view' => 'pdf.loan_schedule',
            'dataKey' => 'schedule',
            'orientation' => 'landscape'
        ];

        $schedule = $this->dispatch(new GetDataForLoanScheduleExport($request, $loan));

        return $this->export($request, $schedule, $options);
    }

    /**
     * @param Request $request
     * @param Loan $loan
     * @return mixed
     */
    public function downloadStatement(Request $request, Loan $loan)
    {
        $options = [
            'filename' => 'loan-statement-'. $loan->number,
            'view' => 'pdf.loan_statement',
            'dataKey' => 'statement',
            'orientation' => 'landscape'
        ];

        $statement = $this->dispatch(new GetDataForLoanStatementExport($request, $loan));

        return $this->export($request, $statement, $options);
    }
}
