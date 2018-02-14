<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 07/01/2017
 * Time: 10:54 PM
 */
$startDate = Carbon\Carbon::parse(request('startDate'))->format('d/m/Y');
$endDate = Carbon\Carbon::parse(request('endDate'))->format('d/m/Y');
?>
@extends('layouts.app')
@section('title', 'Repayments Due')
@section('page-description')
    @if(request()->has('startDate'))
        Showing Repayments due from {{ $startDate }} to {{ $endDate }}
    @else
        Showing all Repayments due that are not fully paid as at today
    @endif
@endsection
@section('page-actions')
    <div class="repayments-filter m-t-md m-b">
        <form action="{{ route('repayments.due') }}" class="form-inline">
            <div class="form-group">
                <select name="credit_officer" id="credit-officer" class="form-control">
                    <option value="">-- Choose Credit Officer --</option>
                    @foreach($creditOfficers as $officer)
                        @if(request('credit_officer') == $officer->id)
                            <option value="{{ $officer->id }}" selected>{{ $officer->getFullName() }}</option>
                        @else
                            <option value="{{ $officer->id }}">{{ $officer->getFullName() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            @include('dashboard.partials._date_range_picker')

        </form>
    </div>
@endsection
@section('content')
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Credit officer</th>
                <th>Client</th>
                <th>Loan</th>
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
            @if($repayments->count())
                @foreach($repayments as $repayment)
                    <tr class="bg bg-{{ $repayment->getStatus()->get('background') }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $repayment->getDueDate() }}</td>
                        <td>{{ $repayment->loan->creditOfficer->getFullName() }}</td>
                        <td>
                            <a href="{{ route('clients.show', ['client' => $repayment->loan->client]) }}">
                            {{ $repayment->loan->client->getFullName() }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('loans.show', ['loan' => $repayment->loan]) }}">
                                {{ $repayment->loan->getDisplayName() }}
                            </a>
                        </td>
                        <td>{{ $repayment->getAmount() }}</td>
                        <td>{{ $repayment->getPrincipal() }}</td>
                        <td>{{ $repayment->getPaidPrincipal() }}</td>
                        <td>{{ $repayment->getInterest() }}</td>
                        <td>{{ $repayment->getPaidInterest() }}</td>
                        <td>{{ $repayment->getOutstandingRepaymentAmount() }}</td>
                        <td>{!! $repayment->getStatus()->get('label') !!}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="11" class="text-center">
                        <h5>There no repayments due for the selected date.</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endsection
