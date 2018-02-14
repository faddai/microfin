<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 01/03/2017
 * Time: 04:00
 */

if ($ledger->isDebitAccount()) {
    $balance = $dr = $ledger->entries->first()->dr ?? 0;
    $cr = $ledger->entries->first()->cr ?? 0;

    if ($dr == 0 && $cr > 0) {
        $balance = -$cr;
    }
} else {
    $balance = $cr = $ledger->entries->first()->cr ?? 0;
    $dr = $ledger->entries->first()->dr ?? 0;

    if ($cr == 0 && $dr > 0) {
        $balance = -$dr;
    }
}
?>
@extends('layouts.app')
@section('title', sprintf('%s (%s) - %s', $ledger->name, $ledger->code, $ledger->category->type))
@section('page-actions')
    <a href="{{ route('accounting.trial_balance.index') }}" class="btn btn-default">
        <i class="fa fa-arrow-circle-left"></i> Back to Trial Balance
    </a>
    <a href="{{ route('accounting.chart') }}" class="btn btn-info"><i class="fa fa-list"></i> View All ledgers</a>
@endsection
@section('page-description', 'View all ledger entries for selected date(s)')
@section('content')
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Txn Date</th>
                <th>Value Date</th>
                <th>Narration</th>
                <th>Dr</th>
                <th>Cr</th>
                <th>Balance</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @if($ledger->entries->count())
                @foreach($ledger->entries as $entry)
                    <?php $balance = $loop->first ? $balance : get_running_balance($balance, $ledger, $entry) ?>
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $entry->created_at->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $entry->transaction ?
                        $entry->transaction->value_date->format(config('microfin.dateFormat')) : $entry->created_at->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $entry->narration }}</td>
                        <td>{{ number_format($entry->dr, 2) }}</td>
                        <td>{{ number_format($entry->cr, 2) }}</td>
                        <td>{{ number_format($balance, 2) }}</td>
                        <td>
                            @if($entry->ledger_transaction_id)
                            <i class="fa fa-list"></i>
                            <a href="{{ route('accounting.transactions.show', ['transaction' => $entry->ledger_transaction_id]) }}">
                                View transaction
                            </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr style="font-weight: bolder">
                    <td colspan="4" style="text-align: right">&nbsp;</td>
                    <td>{{ number_format($ledger->entries->sum('dr'), 2) }}</td>
                    <td>{{ number_format($ledger->entries->sum('cr'), 2) }}</td>
                    <td colspan="2">&nbsp;</td>
                </tr>
            @else
                <tr>
                    <td colspan="11" class="text-center">
                        <h5>There no transactions involving the selected ledger</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endsection
