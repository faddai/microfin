<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/02/2017
 * Time: 21:35
 */
?>
<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default loan-summary-box">
            <div class="panel-body">
                <div class="media">
                            <span class="media-left">
                                <img src="{{ $loan->client->getProfilePhoto() }}" class="img-circle media-object" width="50px"
                                     alt="{{ $loan->client->getFullname() ?? 'n/a' }}">
                            </span>
                    <div class="media-body">
                        <a href="{{ route('clients.show', ['client' => $loan->client]) }}">{{ $loan->client->getFullName() }}</a><br>
                        <strong>Acc: {{ $loan->client->account_number }}</strong><br>
                        @if($loan->client->isIndividual())
                            <strong>{{ ucfirst($loan->client->clientable->gender) }},
                                {{ $loan->client->clientable->dob ? $loan->client->clientable->dob->age .' years' : ''}}</strong><br>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default loan-summary-box">
            <div class="panel-body">
                <strong>Trxn Branch:</strong> {{ $loan->getTransactionBranch() }}<br>
                <strong>Created by:</strong> {{ $loan->createdBy->getFullName() }}<br>
                <strong>Created on:</strong> {{ $loan->created_at->format(config('microfin.dateFormat')) }}<br>
                <strong>Approved on:</strong> {{ $loan->approved_at ? $loan->approved_at->format(config('microfin.dateFormat')) : '' }}<br>
                <strong>Credit Officer:</strong> {{ $loan->creditOfficer->getFullName() }}<br>

            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default loan-summary-box">
            <div class="panel-body">
                <strong>Amount: </strong>{{ $currency }} {{ $loan->amount ? number_format($loan->amount, 2) : 'n/a' }}<br>
                <strong>Repayment Amt: </strong>{{ $currency }} {{ $loan->schedule->count() ? $loan->schedule->first()->getAmount() : 'n/a' }}<br>
                @if($loan->isDisbursed())
                    <strong>Disbursed: </strong>
                    {{ $loan->disbursed_at->format(config('microfin.dateFormat')) }}<br>
                @endif
                <strong>Interest Calc: </strong>{{ $loan->getInterestCalculationStrategy() }}<br>
                <strong>Tenure: </strong>{{ $loan->tenure->label or 'n/a' }}<br>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default loan-summary-box">
            <div class="panel-body">
                <strong>Zone: </strong>{{ $loan->zone->name or 'n/a' }}<br>
                <strong>Product: </strong>{{ $loan->product->name or 'n/a' }}<br>
                <strong>Type: </strong>{{ $loan->type->label or 'n/a' }}<br>
                <strong>Sector: </strong>{{ $loan->sector->name or 'n/a' }}<br>
            </div>
        </div>
    </div>
</div>
