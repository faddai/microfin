<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/12/2016
 * Time: 3:06 PM
 */
?>
@section('content-filter')
    @include('dashboard.partials.loans._filter')
@endsection
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th>#</th>
            <th width="20%">Name</th>
            <th>Amount</th>
            <th>Interest</th>
            <th>Total</th>
            <th>Tenure</th>
            <th>Balance</th>
            <th>Start Date</th>
            <th>Maturity Date</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @if($loans->count())
            @foreach($loans as $loan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('clients.show', ['client' => $loan->client]) }}">
                            {{ $loan->client->getFullName() }}
                        </a>
                    </td>
                    <td>{{ $loan->getPrincipalAmount() }}</td>
                    <td>{{ $loan->getTotalInterest() }} @ {{ number_format($loan->rate, 2) }}%</td>
                    <td>{{ $loan->getTotalLoanAmount() }}</td>
                    <td>{{ $loan->tenure->label }}</td>
                    <td>{{ $loan->getBalance() }}</td>
                    <td>{{ $loan->schedule ? $loan->schedule->first()->due_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $loan->schedule ? $loan->schedule->last()->due_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Action buttons">
                            <a href="{{ route('loans.show', compact('loan')) }}" class="btn btn-success">
                                <i class="fa fa-eye"></i> Details
                            </a>
                            @if($loan->isPending())
                            <a href="{{ route('loans.approve', compact('loan')) }}"
                               class="btn btn-success js-loan-approval">
                                <i class="fa fa-check-square-o"></i> Approve
                            </a>
                            <a href="{{ route('loans.decline', compact('loan')) }}"
                               class="btn btn-danger js-loan-approval">
                                <i class="fa fa-remove"></i> Decline
                            </a>
                            @endif

                            @if($loan->isApproved() && ! $loan->isDisbursed())
                            <a href="{{ route('loans.disburse', compact('loan')) }}"
                               class="btn btn-success js-loan-approval">
                                <i class="fa fa-check-square-o"></i> Disburse
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10">
                    <h5 class="text-center">There are no loans currently.</h5>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
    <div class="text-center">
        {{ $loans->appends($_GET)->links() }}
    </div>
</div>
