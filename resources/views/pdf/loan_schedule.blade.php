<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/06/2017
 * Time: 01:31
 */
?>
@extends('layouts.pdf')
@section('title', 'Loan Schedule')
@section('summary')
<table>
    <tbody>
    <tr>
        @foreach($schedule->meta as $key => $meta)
            <td>
                <div class="col-md-3 m-b">
                    <small>{{ $key }}</small>
                    <div class="font-bold">{{ str_replace(['\'', '"'], '', $meta) }}</div>
                </div>
            </td>
        @endforeach
    </tr>
    </tbody>
</table>
@endsection

@section('content')
    <table class="table table-condensed">
        <thead>
        <tr>
            @foreach($schedule->shift() as $th)
                <th>{{ $th }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($schedule as $_schedule)
            <tr>
                <td>{{ $_schedule['#'] }}</td>
                <td>{{ $_schedule['Due Date'] }}</td>
                <td>{{ $_schedule['Principal'] }}</td>
                <td>{{ $_schedule['Paid principal'] }}</td>
                <td>{{ $_schedule['Interest'] }}</td>
                <td>{{ $_schedule['Paid interest'] }}</td>
                <td>{{ $_schedule['Fees'] }}</td>
                <td>{{ $_schedule['Paid fees'] }}</td>
                <td>{{ $_schedule['Outstanding'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection