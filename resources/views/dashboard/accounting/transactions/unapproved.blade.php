<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 25/03/2017
 * Time: 18:47
 */
?>
@extends('layouts.app')
@section('title', 'Manual G.L. Entry Approval')
@section('page-actions')
    <a href="{{ route('accounting.transactions.create') }}" class="btn btn-info">Create new Transaction</a>
@endsection
@section('page-description', 'Review and post manual transactions to the General Ledger')
@section('content')
    <div class="table-responsive">
        <table class="table" id="gl-entries">
            <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Value Date</th>
                <th>Created by</th>
                <th># Entries</th>
                <th width="40%">Action</th>
            </tr>
            </thead>
            <tbody>
            @if($transactions->count())
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transaction->created_at->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $transaction->created_at->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $transaction->user->getFullName(false) }}</td>
                        <td>{{ $transaction->entries->count() }}</td>
                        <td>
                            <div class="btn-group" role="button">
                                <a href="#view-transaction-modal" class="btn btn-default btn-sm"
                                   data-date="{{ $transaction->created_at->format(config('microfin.dateFormat')) }}"
                                   data-value-date="{{ $transaction->created_at->format(config('microfin.dateFormat')) }}"
                                   data-entries="{{ json_encode($transaction->entries) }}"
                                   data-created-by="{{ $transaction->user->getFullName(false) }}"
                                   data-dr-subtotal="{{ $transaction->entries->sum('dr') }}"
                                   data-cr-subtotal="{{ $transaction->entries->sum('cr') }}"
                                   data-id="{{ $transaction->id }}"
                                   data-toggle="modal"><i class="fa fa-eye"></i> View details
                                </a>

                                <a href="{{ route('accounting.transactions.unapproved.destroy') }}"
                                   data-transaction-id="{{ $transaction->id }}"
                                   class="btn btn-danger btn-sm quick-cancel-transaction">
                                    <i class="fa fa-ban"></i> Cancel transaction
                                </a>

                                <a href="{{ route('accounting.transactions.store') }}"
                                   data-transaction-id="{{ $transaction->id }}"
                                   class="btn btn-success btn-sm quick-approve-transaction">
                                    <i class="fa fa-check"></i> Approve transaction
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center">
                        <h5>There are no pending transactions here currently</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>

        <div class="text-center">
            {{ $transactions->links() }}
        </div>
    </div>
    
    <div id="view-transaction-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content modal-lg">
                <table class="table table-striped m-t">
                    <tr>
                        <td><strong>Transaction Date</strong></td>
                        <td class="date"></td>
                    </tr>
                    <tr>
                        <td><strong>Value Date</strong></td>
                        <td class="value-date"></td>
                    </tr>
                    <tr>
                        <td><strong>Created by</strong></td>
                        <td class="created-by"></td>
                    </tr>
                </table>

                <table class="table table-bordered entries">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Narration</th>
                            <th>Ledger</th>
                            <th>Dr</th>
                            <th>Cr</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <form id="transaction-form" action="{{ route('accounting.transactions.store') }}" method="POST">
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <input type="hidden" name="unapproved_transaction_id" value="">
                    </div>
                    <div class="modal-footer">
                        <div class="text-center">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <a href="{{ route('accounting.transactions.unapproved.destroy') }}"
                           class="btn btn-danger quick-cancel-transaction"><i class="fa fa-ban"></i> Cancel transaction
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Approve & Post transaction to GL
                        </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('more_scripts')
<script type="text/javascript" src="{{ asset('js/manual_gl.js') }}"></script>
@endpush