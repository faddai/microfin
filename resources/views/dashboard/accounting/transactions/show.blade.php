<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/03/2017
 * Time: 11:56
 */
?>
@extends('layouts.app')
@section('title', 'Transaction Details')
@section('page-actions')
    <a href="javascript: window.history.back()" class="btn btn-default"><i class="fa fa-arrow-circle-left"></i> Go back</a>
    <a href="{{ route('accounting.transactions.index') }}" class="btn btn-info"><i class="fa fa-list"></i> View All Transactions</a>
@endsection
@section('page-description', 'All entries posted in this transaction')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <p>Transaction ID: {{ $transaction->uuid }}</p>
            <p>Date: {{ $transaction->created_at->format('d/m/Y') }}</p>
            <p>User: {{ $transaction->user ? $transaction->user->getFullName() : 'n/a' }}</p>
        </div>
    </div>
    <hr>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Narration</th>
                <th>Ledger</th>
                <th>Dr</th>
                <th>Cr</th>
            </tr>
            </thead>
            <tbody>
            @if($transaction->count())
                @foreach($transaction->entries as $entry)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $entry->narration }}</td>
                        <td>{{ $entry->ledger->name }}</td>
                        <td>{{ number_format($entry->dr, 2) }}</td>
                        <td>{{ number_format($entry->cr, 2) }}</td>
                    </tr>
                @endforeach
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
