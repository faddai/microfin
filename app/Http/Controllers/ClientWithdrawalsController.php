<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/02/2017
 * Time: 11:26
 */

namespace App\Http\Controllers;

use App\Entities\Client;
use App\Http\Requests\DepositFormRequest;
use App\Jobs\AddClientWithdrawalJob;


class ClientWithdrawalsController extends Controller
{
    public function store(DepositFormRequest $request)
    {
        try {
            $this->dispatch(new AddClientWithdrawalJob($request, Client::findOrFail($request->get('client_id'))));
        } catch (\Exception $exception) {
            logger()->error('Error occurred whiles saving withdrawal', compact('exception'));

            flash()->error('Error occurred. '. $exception->getMessage());

            return back()->withInput();
        }

        flash()->success('You have successfully Withdrawn an amount of '. request('amount') . ' from Client account.');

        return back();
    }
}