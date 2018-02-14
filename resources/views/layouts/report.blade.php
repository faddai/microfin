<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/04/2017
 * Time: 11:20
 */
use Carbon\Carbon;

$data = array_merge(request()->all(), [
    'report' => request()->segment(3),
    'date' => request('date', new Carbon())->format('d/m/Y'),
    'startDate' => request('startDate', new Carbon())->format('d-m-Y'),
    'endDate' => request('endDate', new Carbon())->format('d-m-Y'),
]);

$printUrl = route('reports.loans.download', array_merge($data, ['format' => 'print']));
$pdfUrl = route('reports.loans.download',  array_merge($data, ['format' => 'pdf']));
$csvUrl = route('reports.loans.download',  array_merge($data, ['format' => 'csv']));
?>
@include('layouts.partials._header')

@if(auth()->check())
    <div id="app">
        @include('dashboard.partials._main_nav')
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="title">@yield('title')</h2>
                    <p>@yield('page-description')</p>
                </div>

                <div class="col-md-6" style="margin-top: 10px; margin-bottom: 10px">
                    <div class="btn-group pull-right" role="group" aria-label="...">
                        @yield('page-actions')
                    </div>
                </div>
            </div>

            @if(request()->segment(3))
                <div class="filter">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="well">
                                @yield('content-filter')
                                @unless(request()->segment(3) === 'crb-monthly-report')
                                <div class="btn-group btn-group-sm" role="group" aria-label="Data Export Buttons">
                                    {{--<a href="{{ $printUrl }}" class="btn btn-default">--}}
                                        {{--<i class="fa fa-print"></i> Print--}}
                                    {{--</a>--}}
                                    {{--<a href="{{ $pdfUrl }}" class="btn btn-default">--}}
                                        {{--<i class="fa fa-file-pdf-o text-danger"></i> Download--}}
                                    {{--</a>--}}
                                    <a href="{{ $csvUrl }}" class="btn btn-default">
                                        <i class="fa fa-file-excel-o text-info"></i> Export as CSV
                                    </a>
                                </div>
                                @endunless
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            @endif

            @include('flash::message')

            @if (isset($errors) && count($errors))
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </div>
    </div>
    @include('layouts.partials._copyright')
@endif

@include('layouts.partials._footer')