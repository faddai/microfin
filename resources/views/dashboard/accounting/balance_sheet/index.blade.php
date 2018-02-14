<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 16/04/2017
 * Time: 16:53
 */
?>
@extends('layouts.app')
@section('title', 'Balance Sheet')
@section('page-description')
    Balance Sheet as at {{ $balanceSheet->date->format(config('microfin.dateFormat')) }}
@endsection
@section('page-actions')
    <form action="" class="form-inline">
        <div class="form-group">
            <input type="text" class="form-control" role="datepicker" name="date" data-date-format="dd/mm/yyyy"
                   value="{{ $balanceSheet->date->format('d/m/Y') }}">
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-md btn-success"><i class="fa fa-search"></i> Go</button>
        </div>
    </form>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-6 pull-right m-b">
            <div class="btn-group btn-group-sm pull-right" role="group" aria-label="Data Export Buttons">
                <a href="{{ route('accounting.balance_sheet.download', ['format' => 'csv']) }}" class="btn btn-default">
                    <i class="fa fa-file-excel-o text-info"></i> Export as CSV
                </a>
            </div>
        </div>

        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td>&nbsp;</td>
                            <td><strong>Balance</strong></td>
                            <td><strong>Budgeted</strong></td>
                        </tr>

                        @foreach($balanceSheet as $ledgerCategoryType => $balances)
                            {{-- Show asset, liab, capital accounts on balance sheet --}}
                            @foreach($balances as $ledgerCategoryName => $ledgersBalances)
                                <tr>
                                    <td class="bg-success"><strong>{{ $ledgerCategoryName }}</strong></td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                @foreach($ledgersBalances->get('ledgers') as $ledger => $details)
                                    <tr>
                                        <td width="50%">{{ $ledger }}</td>
                                        <td class="text-left">{{ number_format($details->get('balance'), 2) }}</td>
                                        <td class="text-left">{{ number_format($details->get('budgeted'), 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-warning">
                                    <td><strong>Subtotal</strong></td>
                                    <td><strong>{{ number_format($ledgersBalances->get('subtotal'), 2) }}</strong></td>
                                    <td><strong>{{ number_format($ledgersBalances->get('subtotal_budgeted'), 2) }}</strong></td>
                                </tr>
                            @endforeach
                            <tr class="bg-danger">
                                <td><strong>Total {{ ucfirst($ledgerCategoryType) }}</strong></td>
                                <td><strong>{{ number_format($balances->total, 2) }}</strong></td>
                                <td><strong>{{ number_format($balances->total_budgeted ?? 0, 2) }}</strong></td>
                            </tr>
                        @endforeach

                        <tr>
                            <td colspan="3">&nbsp;</td>
                        </tr>
                        <tr class="bg-info">
                            <td><strong>Total Liabilities & Capital</strong></td>
                            <td><strong>{{ number_format($balanceSheet->totalLiabilitiesAndCapital, 2) }}</strong></td>
                            <td><strong>{{ number_format($balances->total_budgeted ?? 0, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
