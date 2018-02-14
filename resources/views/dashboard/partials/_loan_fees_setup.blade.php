<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/10/2016
 * Time: 21:17
 */
?>
@if($fees->count())
    <div class="table-responsive">
        <table class="table table-striped table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Value</th>
                <th>Rec. Ledger</th>
                <th>Income Ledger</th>
                <th>Paid upfront</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($fees as $fee)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $fee->name }}</td>
                    <td>{{ ucfirst($fee->type) }}</td>
                    <td>
                        {{ $fee->type === App\Entities\Fee::PERCENTAGE ? $fee->rate.'%' : config('app.currency'). number_format($fee->rate, 2)  }}
                    </td>
                    <td>
                        <a href="{{ route('accounting.ledgers.show', ['ledger' => $fee->receivableLedger->id]) }}">
                            {{ $fee->receivableLedger->name }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('accounting.ledgers.show', ['ledger' => $fee->incomeLedger->id]) }}">
                            {{ $fee->incomeLedger->name }}
                        </a>
                    </td>
                    <td>{{ $fee->is_paid_upfront ? 'Yes' : 'No' }}</td>
                    <td>
                        <div class="btn-group" role="button">
                            <a href="#edit-fee-modal" class="btn btn-default btn-sm" data-toggle="modal"
                               data-id="{{ $fee->id }}"
                               data-name="{{ $fee->name }}"
                               data-rate="{{ $fee->rate }}"
                               data-receivable-ledger="{{ $fee->receivable_ledger_id }}"
                               data-income-ledger="{{ $fee->income_ledger_id }}"
                               data-url="{{ route('settings.fees.update', compact('fee')) }}"
                               data-type="{{ $fee->type }}"
                               data-is-paid-upfront="{{ $fee->isPaidUpfront() }}">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="#delete-fee-modal" data-toggle="modal" class="btn btn-danger btn-sm"
                               data-url="{{ route('settings.fees.delete', compact('fee')) }}"
                               data-name="{{ $fee->name }}">
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
        <p>You haven't added any fee yet.</p>
    </div>
@endif

<div id="edit-fee-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Edit fee</h4>
            </div>
            <form action="{{ route('settings.fees.store') }}" method="post" name="edit-fee-form">
                {{ csrf_field() }}
                <input type="hidden" value="" name="_method">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="control-label">
                            Name <small><em>Choose a name that best describes the fee</em></small>
                        </label>
                        <input type="text" id="name" class="form-control" name="name" placeholder="Insurance">
                    </div>

                    <div class="form-group">
                        <label for="edit_is_paid_upfront" class="label-control">
                            <input type="checkbox" id="edit_is_paid_upfront" name="is_paid_upfront" value="1">
                            Is this Fee paid upfront <small>i.e. deducted from Loan amount before disbursal</small>
                        </label>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fee-type" class="control-label">How do you want this fee to be applied?</label>
                                <select id="fee-type" class="form-control" name="type">
                                    <option value="">-- Select fee type --</option>
                                    @foreach(trans('fee_types') as $type => $description)
                                        <option value="{{ $type }}">{{ $description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group fee-amount hide">
                                <label for="rate" class="label-control">Applicable Rate (%)</label>
                                <input type="text" class="form-control" id="rate" name="rate" placeholder="1.5">
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <h5 class="panel-heading">Setup ledgers for this Fee</h5>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6 receivables">
                                    <div class="form-group">
                                        <label for="receivable_ledger_id" class="control-label">Receivables
                                            @include('dashboard.partials._help_tooltip', ['message' => 'Specify ledger to receive accrued fees on loans that have this fee applied'])
                                        </label>
                                        <select id="receivable_ledger_id" class="form-control" role="select2"
                                                name="receivable_ledger_id">
                                            <option value="">-- Select Receivable Ledger --</option>
                                            @foreach($ledgers as $ledger)
                                                <option value="{{ $ledger->id }}">{{ $ledger->getDisplayName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="income_ledger_id" class="control-label">Income
                                            @include('dashboard.partials._help_tooltip', ['message' => 'Specify ledger to record fee income realised on loans that have this fee applied'])
                                        </label>
                                        <select id="income_ledger_id" class="form-control" role="select2"
                                                name="income_ledger_id">
                                            <option value="">-- Select Income Ledger --</option>
                                            @foreach($ledgers as $ledger)
                                                <option value="{{ $ledger->id }}">{{ $ledger->getDisplayName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
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

<div id="delete-fee-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form name="delete-fee-form" action="#" method="POST">
                <div class="modal-body text-center">
                    <p>
                        <i class="fa fa-exclamation-triangle fa-5x text-danger"></i>
                    </p>

                    <p>Are you sure you want to delete this fee? This action can't be reversed.</p>
                        {{ csrf_field() }}
                    <input type="hidden" name="_method" value="delete">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash-o"></i> Yes, delete <span class="fee-name"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>