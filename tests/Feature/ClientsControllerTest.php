<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

use App\Entities\User;
use App\Jobs\AddClientJob;
use Tests\TestCase;

class ClientsControllerTest extends TestCase
{
    /**
     * Create 7 clients
     * 2 of which are individual and 5 corporate clients
     *
     * @return \Illuminate\Support\Collection
     */
    public function createClients()
    {
        $this->setAuthenticatedUserForRequest();

        return collect([
            [
                'type' => 'individual',
                'account_number' => 3333
            ],
            [
                'type' => 'individual',
                'account_number' => 2222,
                'firstname' => 'Copos',
                'lastname' => 'Due'
            ],
            [
                'type' => 'individual',
                'account_number' => 4444,
                'firstname' => 'Funda',
                'lastname' => 'Mental'
            ],
            [
                'type' => 'corporate',
                'account_number' => 5555,
                'company_name' => 'Escobar Inc',
            ],
            [
                'type' => 'corporate',
                'account_number' => 6666,
                'company_name' => 'Guits Inc',
            ],
            [
                'type' => 'corporate',
                'account_number' => 7777,
                'company_name' => 'Yen Duedio',
            ],
            [
                'type' => 'corporate',
                'account_number' => 8888,
                'company_name' => 'Copular Yen',
            ]
        ])->map(function (array $client) {
            $this->request->replace($client);
            return $this->dispatch(new AddClientJob($this->request));
        });
    }

    public function test_search_for_clients_using_an_account_number()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(['type' => 'individual', 'account_number' => 3333]);

        $client = $this->dispatch(new AddClientJob($this->request));

        // search for client with account number containing 33 and since there's only a single
        // occurrence of such client, get redirected to view the client's details
        $this->actingAs(factory(User::class)->create())
            ->get('clients?q=33')
            ->assertStatus(302)
            ->assertRedirect('clients/'. $client->id);
    }

    public function test_view_client_details_page()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(['type' => 'individual', 'account_number' => 3333]);

        $client = $this->dispatch(new AddClientJob($this->request));

        $this->actingAs(factory(User::class)->create())
            ->get('clients/'. $client['id'])
            ->assertSee('3333');
    }

    public function test_search_for_clients_using_name()
    {
        $user = factory(User::class)->create();
        $clients = $this->createClients();

        $this->actingAs($user)
            ->get('clients?q=funda')
            ->assertStatus(302)
            ->assertRedirect('clients/'. $clients->get(2)->id);

        // do a search that matches more than one Corporate Client
        $this->actingAs($user)
            ->get('clients?q=Yen')->assertStatus(200)
            ->assertSee($clients->last()->getFullName())
            ->assertSee((string)$clients->last()->account_number)
            ->assertSee($clients->get(5)->getFullName())
            ->assertSee((string)$clients->get(5)->account_number);
    }

    public function test_client_search_matching_individual_and_corporate_clients()
    {
        $clients = $this->createClients();

        // search matching both an Individual and a Corporate client
        $this->actingAs(factory(User::class)->create())
            ->get('clients?q=Due')
            ->assertStatus(200)
            ->assertSee($clients->get(1)->getFullName())
            ->assertSee((string)$clients->get(1)->account_number)
            ->assertSee($clients->get(5)->getFullName());
    }

    public function test_search_for_non_existent_client_returns_nothing()
    {
        $user = factory(User::class)->create();

        // search when there are no clients
        $this->actingAs($user)
            ->get('clients?q=cyan')
            ->assertStatus(200)
            ->assertSee('No client was found matching your search');

        $this->createClients();

        // search after there're clients
        $this->actingAs($user)
            ->get('clients?q=cyan')
            ->assertStatus(200)
            ->assertSee('No client was found matching your search');
    }
}
