<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 26/11/2016
 * Time: 4:17 PM
 */
?>
@extends('layouts.app')
@section('title', 'Loan Details #'. $loan->number)
@section('page-actions')
    @if($loan->isPending())
        <a href="{{ route('loans.decline', compact('loan')) }}" class="btn btn-danger js-loan-approval">
            <i class="fa fa-remove"></i> Decline
        </a>
        <a href="{{ route('loans.approve', compact('loan')) }}" class="btn btn-success js-loan-approval">
            <i class="fa fa-check"></i> Approve
        </a>
    @endif

    @if($loan->isApproved() && ! $loan->isDisbursed())
        <a href="#disburse-loan-modal" data-toggle="modal" class="btn btn-success">
            <i class="fa fa-check"></i> Disburse
        </a>
    @endif

    @if($loan->isDisbursed() && ! $loan->isRestructured())
        @if($loan->payoff && $loan->payoff->status === 'pending')
            <button class="btn btn-warning">Pending payoff approval</button>
        @elseif($loan->payoff && $loan->payoff->status === 'approved')
            <label class="btn btn-success" disabled="disabled">Paid off</label>
        @else
            <a href="#payoff-loan-modal" data-toggle="modal" class="btn btn-default">
                <i class="fa fa-check"></i> Payoff
            </a>
            <a href="{{ route('loans.restructure', compact('loan')) }}" class="btn btn-default">
                <i class="fa fa-refresh"></i> Restructure
            </a>
        @endif
    @endif

@endsection
@section('content')
    <div class="col-md-12 m-b-md">
        @include('dashboard.partials.loans._show_loan_summary')
    </div>

    <div class="col-md-12 m-b-md">
        @include('dashboard.partials.loans._show_loan_overview')
    </div>

    <hr>

    <div class="col-md-12 m-b-md">
        @include('dashboard.partials.loans._show_nav_tabs')
        <div class="tab-content m-t">
            @include('dashboard.partials.loans._show_schedule')
            @include('dashboard.partials.loans._show_loan_fees')
            @include('dashboard.partials.loans._show_guarantors')
            @include('dashboard.partials.loans._show_collaterals')
            @include('dashboard.partials.loans._show_restructure')
            @include('dashboard.partials.loans._show_loan_statement')
        </div>
    </div>

    @include('dashboard.partials.loans._disburse_loan_modal_content')
    @include('dashboard.partials.loans._payoff_loan_modal_content')
@endsection
