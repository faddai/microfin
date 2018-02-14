<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 26/02/2017
 * Time: 15:44
 */
?>
@if($products->count())
    <div class="table-responsive">
        <table class="table table-striped table-condensed">
            <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Min Amount</th>
                <th>Max Amount</th>
                <th>Princ. Ledger</th>
                <th>Int. Ledger</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ number_format($product->min_loan_amount ?? 0, 2) }}</td>
                    <td>{{ number_format($product->max_loan_amount ?? 0, 2) }}</td>
                    <td>{{ $product->principalLedger->name or 'n/a' }}</td>
                    <td>{{ $product->interestReceivableLedger->name or 'n/a' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Action buttons">
                            <a href="#edit-product-modal" data-toggle="modal" class="btn btn-default"
                               data-id="{{ $product->id }}"
                               data-url="{{ route('settings.products.update', compact('product')) }}"
                               data-code="{{ $product->code }}"
                               data-principal-ledger="{{ $product->principal_ledger_id }}"
                               data-interest-ledger="{{ $product->interest_ledger_id }}"
                               data-interest-income-ledger="{{ $product->interest_income_ledger_id }}"
                               data-name="{{ $product->name }}"
                               data-description="{{ $product->description }}"
                               data-min="{{ $product->min_loan_amount ?? 0 }}"
                               data-max="{{ $product->max_loan_amount ?? 0 }}" >
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="#delete-product-modal" data-toggle="modal" class="btn btn-danger"
                               data-id="{{ $product->id }}"
                               data-name="{{ $product->name }}"
                               data-url="{{ route('settings.products.delete', compact('product')) }}">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center">
        <i class="fa fa-list-ul fa-5x"></i>
        <p>You haven't added any Loan product yet.</p>
    </div>
@endif

<div id="edit-product-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <h4 class="modal-title">Edit Loan Product</h4>
            </div>
            <form action="" method="POST" name="edit-product-form">
                {{ csrf_field() }}
                <input type="hidden" value="put" name="_method">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="code" class="control-label">Code</label>
                                <input type="text" id="code" class="form-control" name="code" value="{{ old('code') }}"
                                       placeholder="Product code">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="name" class="control-label">Name</label>
                                <input type="text" id="name" class="form-control" name="name" value="{{ old('name') }}"
                                       placeholder="Product name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_loan_amount" class="control-label">Minimum Loan Amount</label>
                                <input type="number" id="min_loan_amount" class="form-control" name="min_loan_amount" value="{{ old('min_loan_amount') }}"
                                       placeholder="Eg. 2000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_loan_amount" class="control-label">Maximum Loan Amount</label>
                                <input type="number" id="max_loan_amount" class="form-control" name="max_loan_amount" value="{{ old('max_loan_amount') }}"
                                       placeholder="Eg. 200000">
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <h5 class="panel-heading">Setup ledgers for this Loan Product</h5>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="principal_ledger_id" class="control-label">Principal Ledger
                                            @include('dashboard.partials._help_tooltip', ['message' => 'Specify ledger to record the Principal given out as loan under this Product'])
                                        </label>
                                        <select id="principal_ledger_id" class="form-control" role="select2"
                                                name="principal_ledger_id">
                                            <option value="">-- Select Principal Ledger --</option>
                                            @foreach($ledgers as $ledger)
                                                <option value="{{ $ledger->id }}">{{ $ledger->getDisplayName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="interest_ledger_id" class="control-label">Interest (Receivable)
                                            @include('dashboard.partials._help_tooltip', ['message' => 'Specify ledger to record daily interest accrued on repayments for this Product'])
                                        </label>
                                        <select id="interest_ledger_id" class="form-control" role="select2"
                                                name="interest_ledger_id">
                                            <option value="">-- Select Interest Ledger --</option>
                                            @foreach($ledgers as $ledger)
                                                <option value="{{ $ledger->id }}">{{ $ledger->getDisplayName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="interest_income_ledger_id" class="control-label">Interest (Income)
                                            @include('dashboard.partials._help_tooltip', ['message' => 'Specify ledger to record interest realised on repayments for this Product'])
                                        </label>
                                        <select id="interest_income_ledger_id" class="form-control" role="select2"
                                                name="interest_income_ledger_id">
                                            <option value="">-- Select Interest Ledger --</option>
                                            @foreach($ledgers as $ledger)
                                                <option value="{{ $ledger->id }}">{{ $ledger->getDisplayName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description" class="control-label">Description</label>
                        <textarea id="description" class="form-control" name="description" rows="4"
                                  placeholder="Describe what this Product is about">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-product-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form name="delete-product-form" action="#" method="POST">
                <div class="modal-body text-center">
                    <p>
                        <i class="fa fa-exclamation-triangle fa-5x text-danger"></i>
                    </p>

                    <p>Are you sure you want to delete this Loan Product? This action cannot be reversed.</p>
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="delete">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash-o"></i> Yes, delete <span class="product-name"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
