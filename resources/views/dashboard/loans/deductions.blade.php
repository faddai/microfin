<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/01/2017
 * Time: 9:54 PM
 */
?>
@extends('layouts.app')
@section('title', 'Effective loan deductions')
@section('page-description')
    @if(request()->has('startDate') && request()->has('endDate'))
        Loan deductions made between {{ $startDate }} and {{ $endDate }}
    @else
        Loan deductions made as at <strong>{{ $endDate }}</strong>
    @endif
@endsection
@section('page-actions')
    <form action="{{ route('loans.deductions') }}" class="form-inline">
        @include('dashboard.partials._date_range_picker')
    </form>
@endsection
@section('content')
    <div class="panel tab-pane" id="schedule">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Loan</th>
                    <th>Client</th>
                    <th>Repayment amount</th>
                    <th>Principal</th>
                    <th>Paid principal</th>
                    <th>Interest</th>
                    <th>Paid interest</th>
                    <th>Outstanding amount</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @if($deductions->count())
                    @foreach($deductions as $deduction)
                        <tr class="bg bg-{{ $deduction->getStatus()->get('background') }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $deduction->repayment_timestamp->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('loans.show', ['loan' => $deduction->loan]) }}">
                                    {{ $deduction->loan->getDisplayName() }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('clients.show', ['client' => $deduction->loan->client]) }}">
                                    {{ $deduction->loan->client->getFullName() }}
                                </a>
                            </td>
                            <td>{{ $deduction->getAmount() }}</td>
                            <td>{{ $deduction->getPrincipal() }}</td>
                            <td>{{ $deduction->getPaidPrincipal() }}</td>
                            <td>{{ $deduction->getInterest() }}</td>
                            <td>{{ $deduction->getPaidInterest() }}</td>
                            <td>{{ $deduction->getOutstandingRepaymentAmount() }}</td>
                            <td>{!! $deduction->getStatus()->get('label') !!}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">
                            <h5>There are no deductions made for the selected date.</h5>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
