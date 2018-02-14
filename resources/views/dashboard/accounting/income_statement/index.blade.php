<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 16/04/2017
 * Time: 16:53
 */
?>
@extends('layouts.app')
@section('title', 'Income Statement')
@section('page-description')
    Income Statement from {{ $incomeStatement->startDate }} to {{ $incomeStatement->endDate }}
@endsection
@section('page-actions')
    <form action="" class="form-inline">
        <div class="form-group">
            <div class="form-control pull-right" role="daterangepicker"
                 style="background: #fff; cursor: pointer;padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <b class="caret"></b>
            </div>
        </div>
        <input type="hidden" id="start-date" name="startDate" value="{{ request('startDate', Carbon\Carbon::today()) }}">
        <input type="hidden" id="end-date" name="endDate" value="{{ request('endDate', Carbon\Carbon::today()) }}">

        <div class="form-group">
            <button type="submit" class="btn btn-md btn-success"><i class="fa fa-search"></i> Go</button>
        </div>
    </form>

@endsection
@section('content')
    <div class="row m-b">
        <div class="col-md-6 pull-right">
            <div class="form-group">
                <div class="btn-group btn-group-sm pull-right" role="group" aria-label="Data Export Buttons">
                    <a href="{{ route('accounting.income_statement.download', array_merge(request()->all(), ['format' => 'csv'])) }}"
                       class="btn btn-default">
                        <i class="fa fa-file-excel-o text-info"></i> Export as CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if($incomeStatement->count())
        @foreach($incomeStatement->reverse() as $categoryName => $statement)
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td colspan="3" class="bg-success"><strong>{{ ucfirst($categoryName) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td><strong>Balance</strong></td>
                                    <td><strong>Budgeted</strong></td>
                                </tr>
                                @foreach($statement->get('ledgers') as $ledger => $details)
                                    <tr>
                                        <td width="50%">{{ $ledger }}</td>
                                        <td class="text-left">{{ number_format($details->get('balance'), 2) }}</td>
                                        <td class="text-left">{{ number_format($details->get('budgeted'), 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-warning">
                                    <td><strong>Subtotal</strong></td>
                                    <td><strong>{{ number_format($statement->get('total'), 2) }}</strong></td>
                                    <td><strong>{{ number_format($statement->get('total_budgeted'), 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-md-12">
                <p>There is nothing here matching your search criteria.</p>
            </div>
        @endif
    </div>

    <div class="row bg-danger">
        <div class="col-md-12 text-center">
            <h5>
                <strong>Net Profit/Loss: {{ number_format($incomeStatement->net_profit->get('balance'), 2) }}</strong>
            </h5>
        </div>
    </div>

    @push('more_styles')
        <style>
            .panel-body { padding-bottom: 0 }
        </style>
    @endpush
@endsection