<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\Ledger;
use App\Jobs\GetTrialBalanceJob;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        $ledgers = $this->dispatch(new GetTrialBalanceJob($request));

        return view('dashboard.accounting.ledgers.trial_balance', compact('ledgers'));
    }

    /**
     * @todo Finish implementation of trial balance export
     * @param Request $request
     * @return mixed
     */
    public function download(Request $request)
    {
        $today = Carbon::today();

        $options = [
            'filename' => 'trial-balance-'. $today->format('d-m-Y'),
        ];

        $trial = $this->dispatch(new GetTrialBalanceJob($request));

        $totalCr = 0;
        $totalDr = 0;

        $trial->transform(function (Ledger $ledger) use (&$totalCr, &$totalDr) {

            $closingBalance = $ledger->getClosingBalance(false);

            $arr = [
                'Code' => $ledger->code,
                'Category' => $ledger->category->name,
                'Ledger' => $ledger->name,
                'Dr' => '',
                'Cr' => '',
            ];

            if ($ledger->isCreditAccount()) {
                // closing is -ve, move it to the other side
                if ($closingBalance < 0) {
                    $arr['Dr'] = number_format(abs($closingBalance), 2);
                } else {
                    $arr['Cr'] = number_format(abs($closingBalance), 2);
                }
            } elseif ($ledger->isDebitAccount()) {
                if ($closingBalance < 0) {
                    $arr['Cr'] = number_format(abs($closingBalance), 2);
                } else {
                    $arr['Dr'] = number_format(abs($closingBalance), 2);
                }
            }

            $totalDr += $ledger->entries->sum('dr');
            $totalCr += $ledger->entries->sum('cr');

            return $arr;
        });

        $trial->push([
            'Code' => '',
            'Category' => '',
            'Ledger' => '',
            'Dr' => number_format($totalDr, 2),
            'Cr' => number_format($totalCr, 2)
        ]);

        $trial->meta = collect([
            'Trial Balance' => '',
            'Date' => sprintf('"%s"', $today->format(config('microfin.dateFormat'))),
        ]);

        return $this->export($request, $trial, $options);
    }
}
