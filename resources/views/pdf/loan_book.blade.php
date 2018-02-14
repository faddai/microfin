<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/06/2017
 * Time: 01:31
 */
$report->shift()
?>
@extends('layouts.pdf')
@section('title', 'Loan Book')
@section('summary')
<table>
    <tbody>
    <tr>
        @if(isset($report->meta))
            @foreach($report->meta as $key => $meta)
                <td>
                    <div class="col-md-3 m-b">
                        <small>{{ $key }}</small>
                        <div class="font-bold">{{ str_replace(['\'', '"'], '', $meta) }}</div>
                    </div>
                </td>
            @endforeach
        @endif
    </tr>
    </tbody>
</table>
@endsection

@section('content')
    <div class="table-responsive">

        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                @foreach($report->shift() as $tableHeader)
                    <th>{{ $tableHeader }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @if($report->count())
                @foreach($report as $_report)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td width="20%">{{ $_report->get('client')->get('name') }}</td>
                        <td>{{ $_report->get('number') }}</td>
                        <td>{{ $_report->get('product') }}</td>
                        <td>{{ $_report->get('type') }}</td>
                        <td>{{ $_report->get('disbursed') }}</td>
                        <td>{{ $_report->get('maturity') }}</td>
                        <td>{{ $_report->get('amount') }}</td>
                        <td>{{ $_report->get('balance') }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bolder">
                    <td colspan="7">&nbsp;</td>
                    <td>{{ number_format($report->totals->get('disbursed'), 2) }}</td>
                    <td>{{ number_format($report->totals->get('balance'), 2) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="10">
                        <h5 class="text-center">There is nothing here matching your search criteria.</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endsection