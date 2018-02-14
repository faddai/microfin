<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/12/2016
 * Time: 3:55 PM
 */

use Carbon\Carbon;

$startDate = Carbon::parse(request('startDate'));
$endDate = Carbon::parse(request('endDate'));
$describe = $startDate->diffInDays($endDate) <= 0 ?
    'today' : sprintf('between %s and %s',
        $startDate->format(config('microfin.dateFormat')),
        $endDate->format(config('microfin.dateFormat'))
    );
?>
@extends('layouts.app')
@section('title', sprintf('Disbursed Loans (%d)', $loans->total()))
@section('page-description')
    A list of loans booked {{ $describe }}.
@endsection
@section('page-actions')
    <a href="{{ route('loans.create') }}" class="btn btn-info">
        <i class="fa fa-plus"></i> New Loan Application
    </a>
@endsection
@section('content')
    @include('dashboard.partials.loans._loans_list')
@endsection

