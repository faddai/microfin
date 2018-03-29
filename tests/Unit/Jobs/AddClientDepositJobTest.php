<?php

use App\Entities\Accounting\Ledger;
use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\User;
use App\Events\DepositAddedEvent;
use App\Events\LoanRepaymentDeductedEvent;
use App\Jobs\AddClientDepositJob;
use Carbon\Carbon;
use Tests\TestCase;

class AddClientDepositJobTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setAuthenticatedUserForRequest();
    }

    public function test_can_add_a_deposit_for_a_client()
    {
        $this->expectsEvents(DepositAddedEvent::class);

        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(ClientTransaction::class)->make(['cr' => 800])->toArray()
        );

        $deposit = $this->dispatch(new AddClientDepositJob(
            $this->request, factory(Client::class, 'individual')->create(['account_balance' => 200]))
        );

        self::assertInstanceOf(ClientTransaction::class, $deposit);
        self::assertEquals(800, $deposit->cr);
        self::assertEquals(0, $deposit->dr);
        self::assertInstanceOf(Client::class, $deposit->client);
        self::assertEquals(1000, $deposit->client->getAccountBalance(false));
    }

    public function test_a_deposit_has_a_transaction_and_value_dates()
    {
        $this->expectsEvents(DepositAddedEvent::class);

        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(ClientTransaction::class)->make(['cr' => 800])->toArray()
        );

        $deposit = $this->dispatch(new AddClientDepositJob(
            $this->request, factory(Client::class, 'individual')->create(['account_balance' => 200]))
        );

        self::assertInstanceOf(ClientTransaction::class, $deposit);
        self::assertNotNull($deposit->created_at);
        self::assertInstanceOf(Carbon::class, $deposit->value_date);
    }

    /**
     * @expectedException \App\Exceptions\ClientDepositException
     */
    public function test_throw_error_adding_a_deposit_without_amount()
    {
        $this->request->merge(
            factory(ClientTransaction::class)->make(['cr' => ''])->toArray()
        );

        $this->doesntExpectEvents(DepositAddedEvent::class);

        $this->dispatch(new AddClientDepositJob($this->request, factory(Client::class)->create()));
    }

    public function test_logged_in_user_gets_all_deposits_transacted_by_him()
    {
        // force auth user to be the same in all requests
        $authUser = $this->request->user();
        $this->request->setUserResolver(function () use ($authUser) {
            return $authUser;
        });

        $this->expectsEvents(DepositAddedEvent::class);

        factory(ClientTransaction::class, 3)
            ->states('deposit')
            ->make()->each(function ($deposit) {
                $this->request->replace($deposit->toArray());

                $this->dispatch(new AddClientDepositJob($this->request, $deposit->client));
            });

        self::assertCount(3, $authUser->clientTransactions);
    }

    public function test_that_client_account_is_credited_with_deposited_amount()
    {
        $client = factory(Client::class, 'individual')->create(['account_balance' => 0]);

        self::assertEquals(0, $client->account_balance);

        $this->request->merge(factory(ClientTransaction::class)->make(['cr' => 2000])->toArray());

        $deposit = $this->dispatch(new AddClientDepositJob($this->request, $client));

        self::assertInstanceOf(ClientTransaction::class, $deposit);
        self::assertEquals(2000, $deposit->cr);
        self::assertEquals(2000, $deposit->client->getAccountBalance(false));
    }

    public function test_adding_deposit_creates_a_ledger_transaction()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(factory(ClientTransaction::class)->make(['cr' => 800])->toArray());

        // add info to create transaction
        $this->request->merge([
            'ledger_id' => Ledger::where('name', 'Cavmount Bank - 90897787')->first()->id,
        ]);

        $deposit = $this->dispatch(new AddClientDepositJob(
            $this->request, factory(Client::class, 'individual')->create(['account_balance' => 0]))
        );

        self::assertInstanceOf(ClientTransaction::class, $deposit);
        self::assertEquals(800, $deposit->cr);
        self::assertInstanceOf(Client::class, $deposit->client);
        self::assertNotNull($deposit->narration);
        self::assertEquals(800, $deposit->client->getAccountBalance(false));
    }

    /**
     * @expectedException App\Exceptions\ClientTransactionException
     * @expectedExceptionMessage You must be assigned to a branch to perform this transaction
     */
    public function test_throws_an_error_if_a_branchless_user_tries_to_do_a_deposit()
    {
        $this->request->setUserResolver(function () {
            return factory(User::class)->create(['branch_id' => '']);
        });

        $this->request->merge(factory(ClientTransaction::class)->states('deposit')->make()->toArray());

        $this->doesntExpectEvents(DepositAddedEvent::class, LoanRepaymentDeductedEvent::class);

        $this->dispatch(new AddClientDepositJob($this->request, factory(Client::class)->create()));
    }
}
