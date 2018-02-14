<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 01/03/2017
 * Time: 06:10
 */

namespace App\Jobs;


use Illuminate\Http\Request;

class GetLedgerEntriesJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * GetLedgerEntriesJob constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}