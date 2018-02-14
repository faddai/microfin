<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/03/2017
 * Time: 16:00
 */
?>
@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading panel-warning">
                    <i class="fa fa-money"></i> Total Loans Value
                    {{--<div class="pull-right">--}}
                        {{--<div class="btn-group">--}}
                            {{--<button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle" aria-expanded="false">--}}
                                {{--<span class="dropdown-label">All</span>--}}
                                {{--<span class="caret"></span>--}}
                            {{--</button>--}}
                            {{--<ul class="dropdown-menu dropdown-select" id="loans-value-filters">--}}
                                {{--<li class="active"><a href="#"><input type="radio" name="d-s-r" checked="">All</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Approved</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Pending</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Declined</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Disbursed</a></li>--}}
                            {{--</ul>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                </div>
                <div class="panel-body">
                    <div class="text-center">
                        <h1><i class="fa fa-bar-chart fa-4x"></i></h1>
                        Sorry, there is not enough data available to draw charts.
                    </div>
                    <div class="row">
                        <div class="m-t-xs b-b"></div>
                        <div class="col-md-12">
                            <div class="col-md-12">
                            <span class="clear">
                                <span class="h3 block m-t-xs text-info">
                                   {{ config('app.currency') }} <span id="total-loans-value">{{ number_format($loansValue, 2)}}</span>
                                </span>
                                <small class="text-muted text-u-c">Total value of loans</small>
                          </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading panel-warning">
                    <i class="fa fa-list-alt"></i> Total Loans Volume
                    {{--<div class="pull-right">--}}
                        {{--<div class="btn-group">--}}
                            {{--<button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle" aria-expanded="false">--}}
                                {{--<span class="dropdown-label">All</span>--}}
                                {{--<span class="caret"></span>--}}
                            {{--</button>--}}
                            {{--<ul class="dropdown-menu dropdown-select" id="loans-value-filters">--}}
                                {{--<li class="active"><a href="#"><input type="radio" name="d-s-r" checked="">All</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Approved</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Pending</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Declined</a></li>--}}
                                {{--<li><a href="#"><input type="radio" name="d-s-r">Disbursed</a></li>--}}
                            {{--</ul>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                </div>
                <div class="panel-body">
                    <div class="text-center">
                        <h1><i class="fa fa-bar-chart fa-4x"></i></h1>
                        Sorry, there is not enough data available to draw charts.
                    </div>
                    <div class="row">
                        <div class="m-t-xs b-b"></div>
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <span class="clear">
                                    <span class="h3 block m-t-xs text-info">
                                        <span id="total-loans-value">{{ $loansCount or 0 }}</span>
                                    </span>
                                    <small class="text-muted text-u-c">Total number of loans</small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading panel-warning">
                    <i class="fa fa-users"></i> Clients
                </div>
                <div class="panel-body">
                    <div class="text-center">
                        <h1><i class="fa fa-bar-chart fa-4x"></i></h1>
                        Sorry, there is not enough data available to draw charts.
                    </div>
                    <div class="row">
                        <div class="m-t-xs b-b"></div>
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <span class="clear">
                                    <span class="h3 block m-t-xs text-info">
                                        <span id="total-loans-value">{{ $clientsCount or 0 }}</span>
                                    </span>
                                    <small class="text-muted text-u-c">Total number of Clients</small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
