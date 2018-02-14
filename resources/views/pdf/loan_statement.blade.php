<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/06/2017
 * Time: 01:31
 */
?>
@extends('layouts.pdf')
@section('title', 'Loan Statement')
@section('summary')
<table>
    <tbody>
    <tr>
        @foreach($statement->meta as $key => $meta)
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
            @foreach($statement->shift() as $th)
                <th>{{ $th }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($statement as $_statement)
            <tr>
                <td>{{ $_statement['#'] }}</td>
                <td>{{ $_statement['Txn Date'] }}</td>
                <td>{{ $_statement['Value Date'] }}</td>
                <td>{{ $_statement['Narration'] }}</td>
                <td>{{ $_statement['Debit'] }}</td>
                <td>{{ $_statement['Credit'] }}</td>
                <td>{{ $_statement['Balance'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection