<?php

use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\User;
use App\Events\ClientWithdrawalEvent;
use App\Jobs\AddClientWithdrawalJob;
use Carbon\Carbon;
use Tests\TestCase;


class AddClientWithdrawalJobTest extends TestCase
{
    public function test_withdrawals_fires_appropriate_event()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(ClientWithdrawalEvent::class);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 2000]);

        self::assertEquals(2000, $client->account_balance);

        $this->request->merge(factory(ClientTransaction::class)->make(['dr' => 1200])->toArray());

        $transaction = $this->dispatch(new AddClientWithdrawalJob($this->request, $client));

        self::assertInstanceOf(ClientTransaction::class, $transaction);
        self::assertTrue($transaction->isWithdrawal());
        self::assertEquals(1200, $transaction->dr);
        self::assertEquals(0, $transaction->cr);
        self::assertEquals(800, $transaction->client->getAccountBalance(false));
    }

    public function test_client_account_balance_is_debited_with_withdrawn_amount()
    {
        $this->setAuthenticatedUserForRequest();

        $client = factory(Client::class, 'individual')->create(['account_balance' => 2000]);

        self::assertEquals(2000, $client->account_balance);

        $this->request->merge(factory(ClientTransaction::class)->make(['dr' => 1200])->toArray());

        $transaction = $this->dispatch(new AddClientWithdrawalJob($this->request, $client));

        self::assertInstanceOf(ClientTransaction::class, $transaction);
        self::assertTrue($transaction->isWithdrawal());
        self::assertEquals(1200, $transaction->dr);
        self::assertEquals(0, $transaction->cr);
        self::assertEquals(800, $transaction->client->getAccountBalance(false));
    }

    /**
     * @expectedException App\Exceptions\ClientTransactionException
     */
    public function test_throw_an_exception_when_debiting_invalid_amount()
    {
        $this->setAuthenticatedUserForRequest();

        $client = factory(Client::class, 'individual')->create(['account_balance' => 2000]);

        self::assertEquals(2000, $client->account_balance);

        $this->request->merge(factory(ClientTransaction::class)->make(['dr' => 0])->toArray());

        $this->dispatch(new AddClientWithdrawalJob($this->request, $client));
    }

    /**
     * @expectedException App\Exceptions\ClientTransactionException
     */
    public function test_throw_an_exception_when_an_attempt_is_made_to_overdraw_account()
    {
        $this->doesntExpectEvents(ClientWithdrawalEvent::class);

        $this->setAuthenticatedUserForRequest();

        $client = factory(Client::class, 'individual')->create(['account_balance' => 2000]);

        self::assertEquals(2000, $client->account_balance);

        $this->request->merge(factory(ClientTransaction::class)->make(['dr' => 80000])->toArray());

        $this->dispatch(new AddClientWithdrawalJob($this->request, $client));
    }

    /**
     * @expectedException App\Exceptions\ClientTransactionException
     * @expectedExceptionMessage You must be assigned to a branch to perform this transaction
     */
    public function test_throws_an_exception_when_a_branchless_user_attempts_to_do_a_withdrawal()
    {
        $this->doesntExpectEvents(ClientWithdrawalEvent::class);

        $this->request->setUserResolver(function () {
            return factory(User::class)->create(['branch_id' => '']);
        });

        $this->request->merge(factory(ClientTransaction::class)->make(['dr' => 1030])->toArray());

        $this->dispatch(new AddClientWithdrawalJob($this->request, factory(Client::class)->create()));
    }

    public function test_a_withdraw_has_a_transaction_and_value_dates()
    {
        $this->expectsEvents(ClientWithdrawalEvent::class);

        $this->setAuthenticatedUserForRequest();

        $this->request->merge(factory(ClientTransaction::class)->make(['dr' => 200])->toArray());

        $deposit = $this->dispatch(new AddClientWithdrawalJob(
            $this->request,
            factory(Client::class, 'individual')->create(['account_balance' => 200]))
        );

        self::assertInstanceOf(ClientTransaction::class, $deposit);
        self::assertNotNull($deposit->created_at);
        self::assertInstanceOf(Carbon::class, $deposit->value_date);
    }
}