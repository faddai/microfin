<?php

use App\Entities\Collateral;
use App\Entities\Loan;
use App\Jobs\AddLoanJob;
use App\Jobs\Reports\GenerateCollateralReportJob;
use Carbon\Carbon;
use Tests\TestCase;

class GenerateCollateralReportJobTest extends TestCase
{
    public function test_can_generate_collateral_report_for_a_pending_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'amount' => 10000,
                    'collaterals' => [
                        factory(Collateral::class)->make(['label' => 'Car', 'market_value' => 42000])->toArray()
                    ],
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $report = $this->dispatch(new GenerateCollateralReportJob($this->request));

        $expected = collect();

        $expected->push(collect([
            'loan' => collect([
                'id' => 1,
                'number' => $loan->number,
                'disbursed_date' => 'n/a', // loan hasn't been disbursed
                'amount' => '10,000.00',
                'product' => 'Staff Loan',
                'type' => $loan->type->label,
            ]),

            'client' => collect([
                'name' => $loan->client->getFullName(),
                'id' => $loan->client->id
            ]),

            'collateral_type' => 'Car',
            'collateral_value' => '42,000.00',
            'percentage_coverage' => (42000 / 10000) * 100,
        ]));

        $expected->title = $expected->description = 'Collateral Report';

        $expectedReportHeader = [
            'Name',
            'Loan Number',
            'Product',
            'Type',
            'Disbursed Date',
            'Loan Amount',
            'Collateral type',
            'Collaterial Value',
            '% Coverage',
        ];

        self::assertEquals($expectedReportHeader, $report->shift());
        self::assertEquals($expected, $report);
    }

    public function test_can_generate_collateral_report_for_a_disbursed_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursedAt = Carbon::today()->subMonth(3);

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'amount' => 10000,
                    'disbursed_at' => $disbursedAt,
                    'collaterals' => [
                        factory(Collateral::class)->make(['label' => 'Car', 'market_value' => 42000])->toArray(),
                    ],
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $report = $this->dispatch(new GenerateCollateralReportJob($this->request));

        $expected = collect();

        $expected->push(collect([
            'loan' => collect([
                'id' => 1,
                'number' => $loan->number,
                'disbursed_date' => $disbursedAt->format(config('microfin.dateFormat')),
                'amount' => '10,000.00',
                'product' => 'Staff Loan',
                'type' => $loan->type->label,
            ]),

            'client' => collect([
                'name' => $loan->client->getFullName(),
                'id' => $loan->client->id
            ]),

            'collateral_type' => 'Car',
            'collateral_value' => '42,000.00',
            'percentage_coverage' => (42000 / 10000) * 100,
        ]));

        $expected->title = $expected->description = 'Collateral Report';

        $expectedReportHeader = [
            'Name',
            'Loan Number',
            'Product',
            'Type',
            'Disbursed Date',
            'Loan Amount',
            'Collateral type',
            'Collaterial Value',
            '% Coverage',
        ];

        self::assertEquals($expectedReportHeader, $report->shift());
        self::assertEquals($expected, $report);
    }

}
