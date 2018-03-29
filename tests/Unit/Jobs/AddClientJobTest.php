<?php

use App\Entities\Branch;
use App\Entities\Client;
use App\Entities\CorporateClient;
use App\Entities\IndividualClient;
use App\Entities\User;
use App\Jobs\AddClientJob;
use Tests\TestCase;


class AddClientJobTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->request->merge([
            'relationship_manager' => factory(User::class)->create()->id,
            'branch_id' => factory(Branch::class)->create()->id
        ]);

        $this->setAuthenticatedUserForRequest();
    }

    /**
     * To create a client, the type of client must be specified
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function test_create_client_without_specifying_client_type_does_not_work()
    {
        $this->dispatch(new AddClientJob($this->request));
    }

    /**
     * Create a client of type Individual
     */
    public function test_create_individual_client()
    {
        $this->request->merge([
            'type' => 'individual',
            'firstname' => faker()->firstName,
            'lastname' => faker()->lastName,
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        self::assertInstanceOf(Client::class, $client);
        self::assertInstanceOf(IndividualClient::class, $client->clientable);
        self::assertEquals($this->request->firstname. ' '. $this->request->lastname, $client->name);
    }

    /**
     * Create a client of type Corporate
     */
    public function test_create_corporate_client()
    {
        $this->request->merge([
            'type' => 'corporate',
            'company_name' => faker()->company,
            'date_of_incorporation' => faker()->date(),
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        self::assertInstanceOf(Client::class, $client);
        self::assertInstanceOf(CorporateClient::class, $client->clientable);
        self::assertEquals($this->request->get('company_name'), $client->name);
    }

    /**
     * Update the company name of a  Corporate client
     **/
    public function test_update_info_of_a_corporate_client()
    {
        $this->request->merge([
            'type' => 'corporate',
            'company_name' => faker()->company,
            'date_of_incorporation' => faker()->date(),
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        $companyName = 'Acme Inc.';
        $this->request->merge(['company_name' => $companyName]);

        // update client
        $updatedClient = $this->dispatch(new AddClientJob($this->request, $client));

        self::assertInstanceOf(CorporateClient::class, $updatedClient->clientable);
        self::assertEquals($companyName, $updatedClient->clientable->company_name);
        self::assertEquals($companyName, $updatedClient->name);
    }

    /**
     * Update the firstname and lastname of an Individual client
     */
    public function test_update_info_of_a_individual_client()
    {
        $this->request->merge([
            'type' => 'individual',
            'firstname' => faker()->firstName,
            'lastname' => faker()->lastName,
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        $firstname = 'Franco';
        $lastname = 'Kiyones';
        $this->request->merge(compact('firstname', 'lastname'));

        $updatedClient = $this->dispatch(new AddClientJob($this->request, $client));

        self::assertInstanceOf(IndividualClient::class, $client->clientable);
        self::assertEquals($firstname, $updatedClient->clientable->firstname);
        self::assertEquals($lastname, $updatedClient->clientable->lastname);
        self::assertEquals('Franco Kiyones', $updatedClient->name);
    }

    public function test_client_name_property_is_set_after_save()
    {
        $this->request->merge([
            'type' => 'individual',
            'firstname' => 'Francis',
            'lastname'  => 'Addai',
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        self::assertEquals('Francis Addai', $client->name);
    }

    public function test_client_name_property_is_updated_after_update()
    {
        $this->request->merge([
            'type' => 'individual',
            'firstname' => 'Francis',
            'lastname'  => 'Addai',
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        self::assertEquals('Francis Addai', $client->name);

        $this->request->merge([
            'lastname' => 'Agyei'
        ]);

        $updatedClient = $this->dispatch(new AddClientJob($this->request, $client));

        self::assertEquals('Francis Agyei', $updatedClient->name);
        self::assertNotEquals('Francis Addai', $updatedClient->name);
    }

    public function test_client_with_hyphenated_name_has_valid_full_name()
    {
        $this->request->merge([
            'type' => 'individual',
            'firstname' => 'Francis',
            'lastname'  => 'Akwasi-Addai',
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        self::assertEquals('FRANCIS AKWASI-ADDAI', $client->getFullName());
        self::assertEquals('Francis Akwasi-Addai', $client->getFullName(false));
    }

    public function test_add_client_photo()
    {
        $filepath = __FILE__;

        $this->request->merge([
            'type' => 'individual',
            'photo' => new \Illuminate\Http\UploadedFile($filepath, 'photo.jpeg', 121212, null, true),
        ]);

        $client = $this->dispatch(new AddClientJob($this->request));

        self::assertNotEmpty($client->photo);
    }
}
