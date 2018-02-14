<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/03/2017
 * Time: 02:27
 */
?>
@extends('layouts.app')
@section('title', 'Manual G.L. Entry')
@section('page-actions')
    <a href="{{ route('accounting.transactions.unapproved.index') }}" class="btn btn-info">
        <i class="fa fa-eye"></i> View Pending Transactions
    </a>
@endsection
@section('page-description', 'Create a transaction to be posted to the General Ledger')
@section('content')
    <div class="table-responsive">
        <form action="{{ route('accounting.transactions.unapproved.store') }}" id="add-gl-entry-form" method="post">
            {{ csrf_field() }}
            <table class="table" id="gl-entries">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Ledger</th>
                    <th>Narration</th>
                    <th>Dr</th>
                    <th>Cr</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                    <tr id="entries-totals" style="font-weight: bolder;">
                        <td colspan="3">&nbsp;</td>
                        <td id="dr-sum">0</td>
                        <td id="cr-sum">0</td>
                    </tr>
                </tbody>
            </table>

            <div class="form-group col-md-6 col-lg-offset-4">
                <a href="#add-gl-entry-modal" data-toggle="modal" class="btn btn-info">
                    <i class="fa fa-plus"></i> Click here to add an Entry
                </a>
                <button type="submit" class="btn btn-success hide" disabled>
                    <i class="fa fa-save"></i> Save transaction
                </button>
            </div>

        </form>
    </div>

    <div id="add-gl-entry-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">

                    <div class="form-group">
                        <label for="ledger-id" class="label-control">Ledger</label>
                        <select id="ledger-id" class="form-control" role="select2">
                            <option value="">-- Select ledger --</option>
                            @foreach($ledgers as $ledger)
                                <option value="{{ $ledger->id }}">{{ $ledger->getDisplayName() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="narration" class="control-label">
                            Narration <small><em>brief description of entry</em></small>
                        </label>
                        <input type="text" id="narration" class="form-control" placeholder="Insurance payout">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type" class="label-control">Type</label>
                                <select id="type" class="form-control" role="select2s" name="type">
                                    <option value="">-- Select type --</option>
                                    <option value="dr">Debit</option>
                                    <option value="cr">Credit</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="value" class="control-label">Value</label>
                                <input type="number" id="value" class="form-control" placeholder="2000">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="js-add-entry"><i class="fa fa-trash-o"></i> Add Entry</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('more_scripts')
<script type="text/javascript" src="{{ asset('js/manual_gl.js') }}"></script>
@endpush