<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/03/2017
 * Time: 13:25
 */
?>
@extends('layouts.app')
@section('title', 'Transactions')
@section('page-description', 'View transactions at '. auth()->user()->branch->name)
@section('content')
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Transaction ID</th>
                <th># Entries</th>
                <th>Dr</th>
                <th>Cr</th>
                <th>Narration</th>
                <th>User</th>
            </tr>
            </thead>
            <tbody>
            @if($transactions->count())
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('accounting.transactions.show', ['transaction' => $transaction->uuid]) }}">
                                {{ $transaction->uuid }}
                            </a>
                        </td>
                        <td>{{ $transaction->entries->count() }}</td>
                        <td>{{ number_format($transaction->entries->sum('dr'), 2) }}</td>
                        <td>{{ number_format($transaction->entries->sum('cr'), 2) }}</td>
                        <td>{{ $transaction->entries->count() ? $transaction->entries->first()->narration : 'n/a' }}</td>
                        <td>{{ $transaction->user ? $transaction->user->getFullName() : 'n/a' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="11" class="text-center">
                        <h5>There no transactions here currently</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>

        <div class="text-center">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection