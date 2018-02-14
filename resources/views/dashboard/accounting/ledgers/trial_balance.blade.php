<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 00:56
 */
$cummulativeCredit = $cummulativeDebit = 0;
?>
@extends('layouts.app')
@section('title', 'Trial Balance')
@section('page-description', 'Viewing trial balance as at today, '. \Carbon\Carbon::today()->format(config('microfin.dateFormat')))
@section('content')
    <div class="table-responsive">
        <div class="col-md-6 pull-right m-b">
            <div class="btn-group btn-group-sm pull-right" role="group" aria-label="Data Export Buttons">
                <a href="{{ route('accounting.trial_balance.download', ['format' => 'csv']) }}" class="btn btn-default">
                    <i class="fa fa-file-excel-o text-info"></i> Export as CSV
                </a>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Category</th>
                <th>Ledger</th>
                <th>Dr</th>
                <th>Cr</th>
            </tr>
            </thead>
            <tbody>
            @if($ledgers->count())
                @foreach($ledgers as $ledger)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{  $ledger->code }}</td>
                        <td>{{  $ledger->category->name }}</td>
                        <td><a href="{{ route('accounting.ledgers.show', compact('ledger')) }}">{{  $ledger->name }}</a></td>
                        {!! $ledger->getClosingBalanceDetailed() !!}
                    </tr>
                    <?php
                    $cummulativeDebit += $ledger->entries->sum('dr');
                    $cummulativeCredit += $ledger->entries->sum('cr');
                    ?>
                @endforeach
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><strong>{{ number_format($cummulativeDebit, 2) }}</strong></td>
                    <td><strong>{{ number_format($cummulativeCredit, 2) }}</strong></td>
                </tr>
            @else
                <tr>
                    <td colspan="11" class="text-center">
                        <h5>There is no trial balance available now</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endsection
