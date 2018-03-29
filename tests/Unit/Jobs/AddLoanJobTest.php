<?php

use App\Entities\Branch;
use App\Entities\Collateral;
use App\Entities\Fee;
use App\Entities\Guarantor;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Entities\User;
use App\Events\LoanCreatedEvent;
use App\Jobs\AddLoanJob;
use Carbon\Carbon;
use Tests\TestCase;


class AddLoanJobTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->request->merge($this->getRequest());

        $this->request->setUserResolver(function () {
            $branch = factory(Branch::class)->create(['name' => 'Madina']);

            return factory(User::class)->create(['name' => 'Kofi Dadzie', 'branch_id' => $branch->id]);
        });
    }

    /**
     * @return array
     */
    private function getRequest()
    {
        $loan = factory(Loan::class)->make([
            'rate' => 2.45,
            'start_date' => Carbon::parse('7 Dec 2016'),
            'grace_period' => 0,
            'user_id' => null
        ])->toArray();

        return array_merge($loan, [
            'guarantors' => factory(Guarantor::class, 2)->make(['job_title' => 'Software Engineer'])->toArray(),
            'collaterals' => factory(Collateral::class, 2)->make(['market_value' => 4500])->toArray(),
        ]);
    }

    public function test_loan_is_approved()
    {
        $loan = factory(Loan::class)->states('approved', 'disbursed')->create();

        self::assertTrue($loan->isApproved());
        self::assertTrue($loan->isDisbursed());
    }
    /**
     * @group loans
     */
    public function test_add_a_new_loan_with_all_required_data()
    {
        $this->expectsEvents(LoanCreatedEvent::class);

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertEquals(2.45, $loan->rate);
        self::assertEquals(12, $loan->tenure->number_of_months);
        self::assertInstanceOf(Guarantor::class, $loan->guarantors->first());
        self::assertInstanceOf(Collateral::class, $loan->collaterals->last());
        self::assertEquals('Software Engineer', $loan->guarantors->first()->job_title);
        self::assertEquals('Software Engineer', $loan->guarantors->last()->job_title);
        self::assertEquals(4500, $loan->collaterals->first()->value());
        self::assertInstanceOf(User::class, $loan->createdBy);

        $loan->schedule->each(function (LoanRepayment $schedule) {
            self::assertNull($schedule->status);
            self::assertNull($schedule->repayment_timestamp);
            self::assertFalse($schedule->has_been_paid);
        });
    }

    public function test_loan_is_registered_by_a_staff()
    {
        $this->expectsEvents(LoanCreatedEvent::class);

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(User::class, $loan->createdBy);
        self::assertNotNull($loan->createdBy->getFullName());
    }

    public function test_loan_is_created_with_status_set_to_pending()
    {
        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(Loan::PENDING, $loan->status);
    }

    public function test_staff_details_does_not_get_updated_when_a_loan_is_updated()
    {
        $authUser = $this->request->user();

        $this->request->merge(['start_date' => Carbon::parse('7 Dec 2016')]);

        $this->expectsEvents(LoanCreatedEvent::class);

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(User::class, $loan->createdBy);
        self::assertEquals(Carbon::parse('7 December 2016'), $loan->start_date);
        self::assertNotEquals('Richard Agyapong', $loan->createdBy->name);
        self::assertEquals($authUser->name, $loan->createdBy->name);
        self::assertEquals($authUser->branch->name, $loan->createdBy->branch->name);

        // set a new logged in user at same branch & update start date
        $user = factory(User::class)->create([
            'name' => 'Richard Agyapong',
            'branch_id' => $loan->createdBy->branch->id
        ]);

        $this->request->setUserResolver(function () use ($user) {
            return $user;
        })->merge(['start_date' => Carbon::parse('January 2, 2017')]);

        $updatedLoan = $this->dispatch(new AddLoanJob($this->request, $loan));

        self::assertInstanceOf(Loan::class, $updatedLoan);
        self::assertEquals(Carbon::parse('January 2 2017'), $updatedLoan->start_date);
        self::assertInstanceOf(User::class, $updatedLoan->createdBy);
        self::assertNotEquals('Richard Agyapong', $updatedLoan->createdBy->name);
        self::assertInstanceOf(Branch::class, $updatedLoan->createdBy->branch);
        self::assertEquals($user->branch->name, $updatedLoan->createdBy->branch->name);
    }

    public function test_that_guarantors_for_a_loan_are_saved()
    {
        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Guarantor::class, $loan->guarantors()->first());
        self::assertEquals(2, $loan->guarantors->count());
    }

    public function test_that_guarantors_for_a_loan_can_be_updated()
    {
        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Guarantor::class, $loan->guarantors()->first());
        self::assertEquals(2, $loan->guarantors->count());

        $this->request->merge([
            'guarantors' => [
                [
                    'guarantor_id' => $loan->guarantors()->first()->id,
                    'name' => 'Francis Asante',
                    'employer' => 'Rancard Solutions Limited'
                ]
            ]
        ]);

        $updatedLoan = $this->dispatch(new AddLoanJob($this->request, $loan));

        self::assertInstanceOf(Loan::class, $updatedLoan);
        self::assertCount(2, $updatedLoan->guarantors);
        self::assertEquals('Francis Asante', $updatedLoan->guarantors()->first()->name);
        self::assertEquals('Rancard Solutions Limited', $updatedLoan->guarantors()->first()->employer);
        self::assertEquals($loan->guarantors->last()->employer, $updatedLoan->guarantors->last()->employer);
    }

    public function test_that_no_guarantor_is_saved_if_request_does_not_contain_data_for_an_actual_guarantor()
    {
        $this->request->replace(factory(Loan::class)->make()->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertNotInstanceOf(Guarantor::class, $loan->guarantors()->first());
        self::assertNotInstanceOf(Collateral::class, $loan->collaterals()->first());
        self::assertEquals(0, $loan->guarantors->count());

        $this->request->replace([
            'guarantors' => [
                [
                    'name' => '',
                    'employer' => ''
                ]
            ]
        ]);

        $updatedLoan = $this->dispatch(new AddLoanJob($this->request, $loan));

        self::assertNotInstanceOf(Guarantor::class, $updatedLoan->guarantors()->first());
        self::assertNotInstanceOf(Collateral::class, $updatedLoan->collaterals()->first());
        self::assertEquals(0, $updatedLoan->guarantors->count());
    }

    public function test_update_an_existing_loan()
    {
        $rate = 5.21;
        $this->request->merge(['rate' => $rate]);

        $this->expectsEvents(LoanCreatedEvent::class);

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertEquals($rate, $loan->rate);

        $rate = 289002;
        $startDate = Carbon::parse('5 Dec 2016');

        $this->request->merge(['rate' => $rate, 'start_date' => $startDate]);

        $updatedLoan = $this->dispatch(new AddLoanJob($this->request, $loan));

        self::assertEquals($rate, $updatedLoan->rate);
        self::assertEquals($startDate, $updatedLoan->start_date);
    }

    /**
     * Create a loan with a tenure of 6 months and check whether it returns a
     * valid maturity date
     */
    public function test_loan_returns_valid_maturity_date()
    {
        $this->request->merge(
            factory(Loan::class)
                ->make(['tenure_id' => Tenure::firstOrCreate(['number_of_months' => 2])->id])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(
            Carbon::today()->addWeekdays($loan->repaymentPlan->number_of_days * 2),
            $loan->maturity_date->startOfDay()
        );
    }

    /**
     * When a grace period is set, add it to the start date
     */
    public function test_that_grace_period_is_applied_to_loan_start_date()
    {
        $this->request->merge(['grace_period' => 3]);

        $expectedLoanStartDate = Carbon::parse($this->request->get('start_date'))->addWeekdays(3);

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals($expectedLoanStartDate, $loan->start_date);
    }

    public function test_that_loan_fees_are_saved()
    {
        $this->request->merge(array_merge(
            factory(Loan::class)
                ->make([
                    'amount' => 10000,
                    'rate' => 9,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
                ])
                ->toArray(),
            ['fees' => [['id' => 1, 'rate' => 17], ['id' => 2, 'rate' => 2.3]]]
        ));

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertInstanceOf(Fee::class, $loan->fees->first());
        self::assertEquals(1700, $loan->fees->first()->pivot->amount);
        self::assertEquals(230, $loan->fees->last()->pivot->amount);
        self::assertEquals(1930, $loan->getTotalFees(false));
    }

    public function test_that_no_loan_fees_are_added()
    {
        $this->request->merge(
            factory(Loan::class)->make([
                'amount' => 10000,
                'rate' => 9,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
                'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id,
            ])->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertNotInstanceOf(Fee::class, $loan->fees->first());
        self::assertEquals(0, $loan->getTotalFees(false));
    }

    public function test_can_calculate_total_fees_for_a_given_loan_amount()
    {
        $this->request->merge(
            factory(Fee::class)
                ->make([
                    'amount' => 10000,
                    'fees' => Fee::select('rate', 'id', 'is_paid_upfront')->get()->toArray()
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(500, $loan->fees->first()->pivot->amount);
        self::assertEquals(5, $loan->fees->first()->pivot->rate);
        self::assertEquals(700, $loan->fees->get(1)->pivot->amount);
        self::assertEquals(7, $loan->fees->get(1)->pivot->rate);
        self::assertEquals(3, $loan->fees->last()->pivot->amount); // fixed insurance amount
        self::assertEquals(0.03, $loan->fees->last()->pivot->rate); // fixed insurance rate
        self::assertEquals(2203, $loan->getTotalFees(false));
    }

    public function test_can_generate_a_valid_loan_number()
    {
        // we need same user logged in during loans creation
        $this->request->setUserResolver(function () {
            return factory(User::class)->create([
                'branch_id' => Branch::findOrFail(2)->id,
                'name' => 'Francis Addai'
            ]);
        });

        $this->request->merge(factory(Loan::class)->make(['user_id' => null])->toArray());

        self::assertEquals(0, Loan::count());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertNotNull($loan->number);
        self::assertEquals($loan->createdBy->branch->code . '0000001', $loan->number);
        self::assertEquals(10, strlen($loan->number));

        // add a second loan and verify that an appropriate loan number gets assigned
        $this->request->merge(factory(Loan::class)->make(['user_id' => null])->toArray());

        self::assertCount(1, Loan::all());

        $loan2 = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan2);
        self::assertNotNull($loan2->number);
        self::assertEquals($loan2->createdBy->branch->code . '0000002', $loan2->number);
        self::assertEquals(10, strlen($loan2->number));

        // add 5 more loans
        factory(Loan::class, 'customer', 21)
            ->make(['user_id' => null])
            ->each(function (Loan $loan) {
                $this->request->replace($loan->toArray());

                $this->dispatch(new AddLoanJob($this->request));
            });

        self::assertCount(23, Loan::all());

        // add another one and check whether you'd get the right loan number
        $this->request->merge(factory(Loan::class)->make(['user_id' => null])->toArray());

        $loan24 = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan24);
        self::assertNotNull($loan24->number);
        self::assertEquals($loan24->createdBy->branch->code . '0000024', $loan24->number);
        self::assertEquals(10, strlen($loan24->number));

    }

    public function test_that_loan_creation_does_not_send_an_email_to_approvers()
    {
        $this->doesntExpectEvents(LoanCreatedEvent::class);

        $loan = $this->dispatch(new AddLoanJob($this->request, null, false));

        self::assertInstanceOf(User::class, $loan->createdBy);
    }

    public function test_updating_a_loan_should_not_send_an_email()
    {
        $this->setAuthenticatedUserForRequest();

        $this->doesntExpectEvents(LoanCreatedEvent::class);

        $loan = factory(Loan::class, 'staff')->create();

        $this->request->merge(['purpose' => 'hello world']);

        $updatedLoan = $this->dispatch(new AddLoanJob($this->request, $loan));

        self::assertInstanceOf(Loan::class, $updatedLoan);
        self::assertEquals('hello world', $updatedLoan->purpose);
    }

    public function test_can_update_a_previously_set_upfront_fee_to_an_amortised_fee()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'fees' => [1 => ['rate' => 10, 'id' => 1]]
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertTrue($loan->fees->first()->isPaidUpfront());
        self::assertFalse((bool) $loan->fees->first()->pivot->is_paid_upfront);
    }

    public function test_that_loan_fees_with_zero_rate_are_not_saved()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'fees' => [
                        1 => ['rate' => 5.3, 'id' => 1],
                        2 => ['rate' => '0', 'id' => 2],
                    ]
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertCount(1, $loan->fees);
    }
}
