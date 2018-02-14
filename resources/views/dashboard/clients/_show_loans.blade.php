<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 19/03/2017
 * Time: 19:43
 */
?>
<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Loan Number</th>
            <th>Amount</th>
            <th>Balance</th>
            <th>Created</th>
            <th>Maturity</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @if($client->loans->count())
            @foreach($client->loans as $loan)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><a href="{{ route('loans.show', compact('loan')) }}">{{ $loan->number }}</a></td>
                <td>{{ $loan->getPrincipalAmount() }}</td>
                <td>{{ $loan->getBalance() }}</td>
                <td>{{ $loan->created_at->format(config('microfin.dateFormat')) }}</td>
                <td>{{ $loan->maturity_date->format(config('microfin.dateFormat')) }}</td>
                <td>@include('dashboard.partials.loans._status_label')</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6">
                    <h5 class="text-center">There are no loans for this Client.</h5>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
