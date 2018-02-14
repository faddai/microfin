<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 08/05/2017
 * Time: 14:33
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
@section('title', sprintf('Loan Applications (%d)', $loans->total()))
@section('page-description')
    A list of loans created {{ $describe }} awaiting approval and disbursement.
@endsection
@section('page-actions')
    <a href="{{ route('loans.create') }}" class="btn btn-info">
        <i class="fa fa-plus"></i> New Loan Application
    </a>
@endsection
@section('content')
    @include('dashboard.partials.loans._loans_list')
@endsection