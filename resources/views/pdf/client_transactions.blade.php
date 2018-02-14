<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/06/2017
 * Time: 01:31
 */
?>
@extends('layouts.pdf')
@section('title', 'Customer Statement')
@section('summary')
<table>
    <tbody>
    <tr>
        <td>
            <div class="col-md-3 m-b">
                <small>Customer Name</small>
                <div class="font-bold">{{ $transactions->meta->get('Customer Name') }}</div>
            </div>
        </td>
        <td>
            <div class="col-md-3 m-b">
                <small>Customer Number</small>
                <div class="font-bold">{{ str_replace('\'', '', $transactions->meta->get('Customer Number')) }}</div>
            </div>
        </td>
        <td>
            <div class="col-md-3 m-b">
                <small class="">From Date</small>
                <div class="font-bold">{{ str_replace('"', '', $transactions->meta->get('From Date')) }}</div>
            </div>
        </td>
        <td>
            <div class="col-md-3 m-b">
                <small>To Date</small>
                <div class="font-bold">{{ str_replace('"', '', $transactions->meta->get('To Date')) }}</div>
            </div>
        </td>
    </tr>
    </tbody>
</table>
@endsection

@section('content')
    <table class="table table-condensed">
        <thead>
        <tr>
            <th>#</th>
            @foreach($transactions->shift() as $th)
                <th>{{ $th }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $transaction)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $transaction['Value Date'] }}</td>
                <td>{{ $transaction['Narration'] }}</td>
                <td>{{ $transaction['Dr'] }}</td>
                <td>{{ $transaction['Cr'] }}</td>
                <td>{{ $transaction['Balance'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection