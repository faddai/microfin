<?php

namespace App\Jobs;

use App\Entities\CorporateClient;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;

class AddCorporateClientJob
{
    use Queueable;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var CorporateClient
     */
    private $client;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param CorporateClient $client
     */
    public function __construct(Request $request, CorporateClient $client = null)
    {
        $this->request = $request;

        $this->client = null === $client ? new CorporateClient() : $client;
    }

    /**
     * Execute the job.
     *
     * @return CorporateClient
     */
    public function handle()
    {
        foreach ($this->client->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->client->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->client->save();

        return $this->client;
    }
}
