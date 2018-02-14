<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/04/2017
 * Time: 13:06
 */
?>
@extends('layouts.report')
@section('title', $report->title)
@section('page-description', $report->description)
@section('page-actions')
    <a href="{{ route('reports.loans.index') }}" class="btn btn-info">
        <i class="fa fa-line-chart"></i> View all reports
    </a>
@endsection
@section('content')
    @include('dashboard.reports.loans.'. $report->get('view', str_slug(request()->segment(3), '_')))
@endsection
