<?php

namespace App\Jobs;

use App\Entities\Client;
use App\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class AddClientJob
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Client
     */
    private $client;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Client $client
     */
    public function __construct(Request $request, Client $client = null)
    {
        $this->request = $request;
        $this->user = $request->user();
        $this->client = $client ?? new Client([
                'created_by' => $this->user->id,
                'account_number' => $this->generateClientAccountNumber()
            ]);
    }

    /**
     * Execute the job.
     *
     * @return Client
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function handle()
    {
        return DB::transaction(function () {

            // add details of this client
            $client = $this->addOrUpdateClient();

            // save the type of client
            $this->saveMorphRelation($client);

            // profile photo and signature
            $client = $this->saveFiles($client);

            return $client;
        });
    }

    /**
     * @return Client
     */
    private function addOrUpdateClient(): Client
    {
        foreach ($this->client->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->client->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->client->save();
        
        return $this->client;
    }

    /**
     * @param $client
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return Client
     */
    private function saveMorphRelation(Client $client): Client
    {
        $clientType = $this->request->get('type');

        switch ($clientType) {
            case 'individual':
                $this->setIndividualClientName($client);
                $clientable = dispatch_now(new AddIndividualClientJob($this->request, $client->clientable));
                break;
            case 'corporate':
                $this->setCorporateClientName($client);
                $clientable = dispatch_now(new AddCorporateClientJob($this->request, $client->clientable));
                break;
            default:
                throw new BadRequestHttpException(
                    'Invalid client type provided. Valid Clients are [individual, corporate]'
                );
        }

        $client->clientable()->associate($clientable)->save();

        return $client;
    }

    private function setIndividualClientName(Client $client)
    {
        $name = $this->request->get('firstname') .' ';

        if ($middlename = $this->request->get('middlename')) {
            $name .= $middlename. ' ';
        }

        $name .= $this->request->get('lastname');

        $client->name = $name;

        return $client;
    }

    private function setCorporateClientName(Client $client)
    {
        $client->name = $this->request->get('company_name');

        return $client;
    }

    private function saveFiles(Client $client): Client
    {
        if ($this->request->hasFile('photo')) {
            $photo = $this->request->file('photo');
            $filename = 'photo_'. $client->account_number. '.' .$photo->extension();

            $client->update(['photo' => $photo->storeAs('clients', $filename)]);
        }

        if ($this->request->hasFile('signature')) {
            $signature = $this->request->file('signature');
            $filename = 'sign_'. $client->account_number. '.' .$signature->extension();

            $client->update(['signature' => $signature->storeAs('signatures', $filename)]);
        }

        return $client;
    }

    public function generateClientAccountNumber()
    {
        $lastClient =  Client::all()->last();

        return null === $lastClient ?
            sprintf('%s00001', $this->request->user()->branch->code) : sprintf('00%d', $lastClient->account_number + 1);
    }
}