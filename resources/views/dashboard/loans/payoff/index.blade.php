<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 04/06/2017
 * Time: 17:31
 */
use App\Entities\Loan;

$loan = new Loan();
?>
@extends('layouts.app')
@section('title', 'Loan Payoff')
@section('page-description')
    List of Loans pending Payoff
@endsection
@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th width="20%">Name</th>
                <th>Loan</th>
                <th>User</th>
                <th>Amount</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Fees</th>
                <th>Penalty Charged</th>
                <th width="10%">Maturity Date</th>
                <th>Remarks</th>
                <th width="16%">Action</th>
            </tr>
            </thead>
            <tbody>
            @if($payoffs->count())
                @foreach($payoffs as $payoff)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('clients.show', ['client' => $payoff->loan->client]) }}">
                                {{ $payoff->loan->client->getFullName() }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('loans.show', ['loan' => $payoff->loan]) }}">
                                {{ $payoff->loan->number }}
                            </a>
                        </td>
                        <td>{{ $payoff->createdBy->getFullName() }}</td>
                        <td>{{ number_format($payoff->amount, 2) }}</td>
                        <td>{{ number_format($payoff->principal, 2) }}</td>
                        <td>{{ number_format($payoff->interest, 2) }}</td>
                        <td>{{ number_format($payoff->fees, 2) }}</td>
                        <td>{{ number_format($payoff->penalty, 2) }}</td>
                        <td>{{ $payoff->loan->maturity_date->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $payoff->remarks or 'n/a'}}</td>
                        <td>
                            <a href="#decline-payoff-loan-modal" class="btn btn-danger btn-sm" data-toggle="modal"
                               data-action="{{ route('loans.payoff.decline', compact('payoff')) }}">
                                <i class="fa fa-close"></i> Decline
                            </a>
                            <a href="#payoff-loan-modal" class="btn btn-success btn-sm" data-toggle="modal"
                               data-action="{{ route('loans.payoff.approve', compact('payoff')) }}"
                               data-id="{{ $payoff->id }}"
                               data-amount="{{ number_format($payoff->amount, 2) }}"
                               data-principal="{{ number_format($payoff->principal, 2) }}"
                               data-interest="{{ number_format($payoff->interest, 2) }}"
                               data-fees="{{ number_format($payoff->fees, 2) }}"
                               data-penalty="{{ number_format($payoff->penalty, 2) }}"
                               data-remarks="{{ $payoff->remarks }}"
                               data-maturity="{{ $payoff->loan->maturity_date->format(config('microfin.dateFormat')) }}"
                               data-creator="{{ $payoff->createdBy->getFullName() }}"
                               data-created-at="{{ $payoff->created_at->format(config('microfin.dateFormat')) }}"
                               data-client="{{ $payoff->loan->client->getFullName() }}"
                               data-loan-balance="{{ $payoff->loan->getBalance() }}"
                               data-loan-number="{{ $payoff->loan->number }}">
                                <i class="fa fa-check"></i> Approve
                            </a>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="15">
                        <h5 class="text-center">There are no loans awaiting payoff currently.</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    @include('dashboard.partials.loans._payoff_loan_modal_content')

    <div id="decline-payoff-loan-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Decline Loan Payoff</h4>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">

                            {{ csrf_field() }}

                            <div class="form-group">
                                <label for="decline-reason" class="control-label">
                                    Please specify your reason for declining the payoff
                                </label>
                                <textarea name="decline_reason" id="decline-reason" rows="4" autofocus
                                          class="form-control"></textarea>
                            </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" disabled>
                            <i class="fa fa-close"></i>
                            Decline Payoff
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection