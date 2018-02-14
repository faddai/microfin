<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/04/2017
 * Time: 10:28
 */
?>
@extends('layouts.report')
@section('title', 'Loan Reports')
@section('page-description', 'A list of all available loan reports')
@section('content')

    @if($reports->count())
        @foreach($reports->chunk(4) as $chunk)
            <div class="row clearfix">
                @foreach($chunk as $report)
                    <a href="{{ $report['url'] }}">
                        <div class="col-md-3">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <h5>{{ $report->get('title') }}</h5>
                                    <small>{{ str_limit($report->get('description')) }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        @endforeach
    @else
        <div class="panel panel-default">
            <div class="panel-body">
                <h5>There are currently no reports avialable for display</h5>
            </div>
        </div>
    @endif

@endsection
