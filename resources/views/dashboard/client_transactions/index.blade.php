<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/12/2016
 * Time: 19:47
 */
?>

@extends('layouts.app')
@section('title', 'Client Transactions')
@section('page-actions')
    <a href="#add-client-deposit-modal" data-toggle="modal" class="btn btn-info">Add deposit</a>
    <a href="#add-client-withdrawal-modal" data-toggle="modal" class="btn btn-info">Withdraw funds</a>
@endsection
@section('page-description', 'Showing All Clients\' Deposits and Withdrawals at the Lusaka branch')
@section('content')
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Txn Date</th>
                <th>Value Date</th>
                <th>Client</th>
                <th>Account</th>
                <th>Amount ({{ config('app.currency') }})</th>
                <th>Narration</th>
                {{--<th>Receipt</th>--}}
            </tr>
            </thead>
            <tbody>
            @if($transactions->count())
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transaction->created_at->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $transaction->value_date ? $transaction->value_date->format(config('microfin.dateFormat')) : 'n/a' }}</td>
                        <td>
                            <a href="{{ route('clients.show', ['client' => $transaction->client]) }}">
                                {{ $transaction->client->getFullName() }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('accounting.ledgers.show', ['ledger' => $transaction->ledger]) }}">
                                {{ $transaction->ledger ? $transaction->ledger->name : 'n/a' }}
                            </a>
                        </td>
                        <td>{{ number_format($transaction->isDeposit() ? $transaction->cr : $transaction->dr, 2) }}</td>
                        <td>{{ $transaction->narration }}</td>
                        {{--<td><i class="fa fa-print"></i> <a href="#">Print</a></td>--}}
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6">
                        <h5 class="text-center">There are no transactions here currently.</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
        <div class="text-center">
            {{ $transactions->links() }}
        </div>
    </div>

    @include('dashboard.client_transactions._client_deposit_modal_content')
    @include('dashboard.client_transactions._client_withdrawal_modal_content')
@endsection
