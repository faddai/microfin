<?php

namespace App\Jobs;

use App\Entities\IndividualClient;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddIndividualClientJob
{
    use Queueable;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var IndividualClient
     */
    private $client;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param IndividualClient $client
     */
    public function __construct(Request $request, IndividualClient $client = null)
    {
        $this->request = $request;
        $this->client = null === $client ? new IndividualClient : $client;
    }

    /**
     * Execute the job.
     *
     * @return IndividualClient
     */
    public function handle()
    {
        return DB::transaction(function () {

            return $this->saveOrUpdateIndividualClient();

        });
    }

    /**
     * @return IndividualClient
     */
    private function saveOrUpdateIndividualClient()
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
