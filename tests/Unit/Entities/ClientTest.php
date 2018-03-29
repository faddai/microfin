<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

use App\Entities\Branch;
use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\User;
use App\Events\DepositAddedEvent;
use App\Jobs\AddClientDepositJob;
use App\Jobs\AddClientJob;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function test_able_to_get_client_account_balance()
    {
        $this->setAuthenticatedUserForRequest();

        $client = factory(Client::class)->create(['account_balance' => 0]);

        self::assertEquals(0, $client->getAccountBalance());

        $this->expectsEvents(DepositAddedEvent::class);

        $this->request->merge(
            factory(ClientTransaction::class)
            ->make(['cr' => 3200, 'client_id' => $client->id])
            ->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        self::assertEquals(3200, $client->getAccountBalance(false));
    }

    public function test_able_to_deduct_from_client_account_balance()
    {
        $this->setAuthenticatedUserForRequest();

        $client = factory(Client::class)->create(['account_balance' => 0]);

        self::assertFalse($client->isDeductable());

        $this->expectsEvents(DepositAddedEvent::class);

        $this->request->merge(
            factory(ClientTransaction::class)
            ->make(['cr' => 3200, 'client_id' => $client->id])
            ->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        self::assertTrue($client->isDeductable());
    }

    public function test_can_generate_account_number_for_client()
    {
        $user = factory(User::class)->create(['branch_id' => Branch::whereCode('001')->first()->id]);

        $this->request->setUserResolver(function () use ($user) {
            return $user;
        });

        // 001 -> branch_id
        // 00006 -> incremental 5 digit
        $this->request->merge(
            factory(Client::class)->make()->toArray() + ['type' => 'individual']
        );

        $client = $this->dispatch(new AddClientJob($this->request));

        self::assertEquals('00100001', $client->account_number);

        // add more clients
        factory(Client::class, 3)->make()
            ->each(function (Client $client) {
                $this->request->replace(array_merge($client->toArray(), ['type' => 'corporate']));

                $this->dispatch(new AddClientJob($this->request));
            });

        self::assertCount(4, Client::all());
        self::assertEquals('00100004', Client::all()->last()->account_number);
    }
}
