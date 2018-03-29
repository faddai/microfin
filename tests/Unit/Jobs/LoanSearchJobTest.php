<?php

use App\Entities\Client;
use App\Entities\Loan;
use App\Entities\Role;
use App\Jobs\ApproveLoanJob;
use App\Jobs\LoanSearchJob;
use Tests\TestCase;

class LoanSearchJobTest extends TestCase
{
    public function test_can_search_for_pending_loans_created_today()
    {
        // create loans her
        factory(Loan::class, 'customer')->create();

        $this->request->merge(['status' => Loan::PENDING]);

        $loans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertEquals(1, $loans->total());
    }

    public function test_can_search_for_pending_loans_belonging_to_a_credit_officer()
    {
        $creditOfficer = $this->createUserWithARole(Role::CREDIT_OFFICER, 1, ['name' => 'Francis Addai']);

        factory(Loan::class, 5, 'customer')
            ->create()
            ->each(function (Loan $loan, $i) use ($creditOfficer) {
                if ($i > 2) {
                    $loan->update(['credit_officer' => $creditOfficer->id]);
                }
            });

        $this->request->merge([
            'credit_officer' => 1,
            'status' => Loan::PENDING,
            'startDate' => Loan::first()->created_at,
            'endDate' => Loan::first()->created_at,
        ]);

        $loans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertEquals(2, $loans->total());
    }

    public function test_can_search_for_approved_loans_belonging_to_a_credit_officer()
    {
        $this->setAuthenticatedUserForRequest();

        $creditOfficer = $this->createUserWithARole(Role::CREDIT_OFFICER, 1, ['name' => 'Francis Addai']);

        factory(Loan::class, 5)
            ->create()
            ->each(function (Loan $loan, $i) use ($creditOfficer) {
                if ($i > 2) {
                    $loan->update(['credit_officer' => $creditOfficer->id]);

                    $this->dispatch(new ApproveLoanJob($this->request, $loan));
                }
            });

        $this->request->merge([
            'credit_officer' => 1,
            'status' => Loan::APPROVED,
            'startDate' => Loan::first()->created_at,
            'endDate' => Loan::first()->created_at,
        ]);

        $loans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertEquals(2, $loans->total());
    }

    public function test_can_search_for_loans_using_client_account_number()
    {
        $clientAccountNumbers = [112000901, 113001901, 112001901, 110012901, 2930239];

        collect($clientAccountNumbers)
            ->each(function (int $accountNumber) {

                $client = factory(Client::class, 'individual')->create(['account_number' => $accountNumber]);

//                // assign the last loan to the last but one Client so he ends up with 2 loans
//                if ($i === 4) {
//                    $client = Client::find(3);
//                }
//
                factory(Loan::class)->create(['client_id' => $client->id]);
            });

        $this->request->merge(['term' => '113001901']);

        $loans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertEquals(1, $loans->total());

        $this->request->merge(['term' => '110012901']);

        $loans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertEquals(1, $loans->total());
    }

    public function _test_can_search_for_loans_using_client_name()
    {
        $clients = ['Shadrack K', 'Obed Dien', 'Omar Sterling', 'Richlove Taylor', 'Franklyn Cudjoe'];

        collect($clients)
            ->each(function (string $clientName) {

                $client = factory(Client::class, 'individual')->create();

                list($firstname, $lastname) = explode(' ', $clientName);

                $client->clientable()->update(compact('firstname', 'lastname'));

                factory(Loan::class)->create(['client_id' => $client->id]);
            });

        $this->request->merge(['term' => 'obed']);

        $loans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertEquals(1, $loans->total());
    }
}
