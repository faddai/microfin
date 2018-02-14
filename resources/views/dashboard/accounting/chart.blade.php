<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/01/2017
 * Time: 12:16 AM
 */
$counter = 1;
?>
@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('page-actions')
    <a href="#add-ledger-category-modal" class="btn btn-info" data-toggle="modal">
        <i class="fa fa-plus"></i> Add Account Category
    </a>

    <a href="#add-ledger-modal" class="btn btn-info" data-toggle="modal">
        <i class="fa fa-plus"></i> Add Ledger
    </a>
@endsection
@section('content')
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Ledger</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @if($ledgerCategories->count())
                @foreach($ledgerCategories as $category)
                    <tr>
                        <td colspan="4" class="bg-warning text-center"><strong>{{ $category->name }}</strong></td>
                    </tr>
                    @foreach($category->ledgers as $ledger)
                        <tr>
                            <td>{{ $counter }}</td>
                            <td>{{ $ledger->code }}</td>
                            <td>{{ $ledger->name }}</td>
                            <td>
                                <a href="#add-ledger-modal" data-toggle="modal" class="text-primary js-edit-ledger"
                                   data-id="{{ $ledger->id }}"
                                   data-code="{{ $ledger->code }}"
                                   data-name="{{ $ledger->name }}"
                                   data-category="{{ $category->id }}"
                                   data-category-type="{{ $category->type }}"
                                   data-is-bank-or-cash="{{ $ledger->is_bank_or_cash }}"
                                   data-url="{{ route('accounting.ledgers.update', compact('ledger')) }}">
                                <i class="fa fa-pencil"></i> edit
                                </a>&nbsp;
                                <a href="{{ route('accounting.ledgers.show', compact('ledger')) }}">
                                    <i class="fa fa-eye"></i> view
                                </a>
                            </td>
                        </tr>
                        <?php $counter++ ?>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="11" class="text-center">
                        <h5>There no ledgers set up yet.</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <div id="add-ledger-category-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-ledger-category-form" action="{{ route('accounting.ledgers.category.store') }}"
                      method="POST">
                    <header class="modal-header">Add a Ledger Category</header>

                    <div class="modal-body">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label for="name">Category name</label>
                            <input type="text" class="form-control" name="name" id="category" value="{{ old('name') }}"
                                   placeholder="Non Current Liabilities">
                        </div>

                        <div class="form-group">
                            <label for="type">Category type</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">-- Select Category type --</option>
                                 @foreach($ledgerCategoryTypes as $type)
                                    <option value="{{ $type }}">
                                        {{ str_contains($type, 'liab') ? 'Liability' : ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="add-ledger-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-ledger-form" action="{{ route('accounting.ledgers.store') }}" method="POST">
                    <header class="modal-header">Add a Ledger</header>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label for="category-id">Select Ledger Category</label><br>
                            <select class="form-controls" name="category_id" id="category-id" role="select2">
                                <option value="">-- Select Category for Account --</option>
                                @foreach($ledgerCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ledger-name">Ledger name</label>
                            <input type="text" class="form-control" name="name" id="ledger-name"
                                   value="{{ old('name') }}" placeholder="Other Receivables">
                        </div>
                        <div class="form-group">
                            <label for="is-bank-or-cash-ledger">
                                <input type="checkbox" name="is_bank_or_cash" id="is-bank-or-cash-ledger" value="1"
                                        {{ old('is_bank_or_cash') ==  1 ? 'checked' : ''}}>
                                Bank or Cash Ledger
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save Ledger
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('more_scripts')
    <script type="text/javascript">
        (function ($) {

            var selectors = {
                'ledgerName': '#ledger-name',
                'categoryId': '#category-id',
                'isBankOrCashLedger': '#is-bank-or-cash-ledger',
                'addLedgerModal' : '#add-ledger-modal',
                'addLedgerModalHeader' : '.modal-header',
                'addLedgerForm': '#add-ledger-form',
            };

            var originalModalHeader = $(selectors.addLedgerModal).find(selectors.addLedgerModalHeader).text(),
                originalFormAction = $(selectors.addLedgerForm).attr('action');

            // edit ledger
            $(selectors.addLedgerModal).on('show.bs.modal', function(e) {
                // Initialize the button
                var $button = $(e.relatedTarget),
                    ledger = $button.data(),
                    modal = $(this);

                console.log(ledger);

                $(selectors.addLedgerForm).attr('action', ledger.url);

                // an update request
                if (ledger.id) {
                    $('<input>', {
                        value: 'put',
                        type: 'hidden',
                        name: '_method',
                    }).appendTo(selectors.addLedgerForm);

                    modal.find(selectors.addLedgerModalHeader).text('Edit Ledger');
                }

                // Update modal header and content
                modal.find(selectors.ledgerName).val(ledger.name);
                modal.find(selectors.categoryId +' option[value="'+ ledger.category +'"]').prop('selected', true).change();
                ledger.isBankOrCash && modal.find(selectors.isBankOrCashLedger).prop('checked', true).change();
            });

            // Reset modal content
            $(selectors.addLedgerModal).on('hidden.bs.modal', function () {
                $(selectors.addLedgerForm).find('input[name="_method"]').remove();
                $(this).find(selectors.addLedgerModalHeader).text(originalModalHeader);
                $(this).find(selectors.addLedgerForm).attr('action', originalFormAction);
                $(this).find(selectors.categoryId +' option[value=""]').prop('selected', true).change();
                $(this).find(selectors.isBankOrCashLedger).prop('checked', false).change();
            });

        })(jQuery)
    </script>
    @endpush
@endsection
