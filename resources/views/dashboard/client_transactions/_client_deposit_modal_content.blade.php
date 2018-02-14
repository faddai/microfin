<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/02/2017
 * Time: 16:32
 */
?>
<div id="add-client-deposit-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <h4 class="modal-title">Add Client Deposit</h4>
            </div>
            <form action="{{ route('client.transactions.deposits.store') }}" method="POST" name="add-client-deposit-form">
                {{ csrf_field() }}

                <div class="modal-body">
                    <div class="form-group {{ has_error('client_id') }}">
                        <label for="client_id" class="label-control block">Client Account</label>
                        <select name="client_id" id="client_id" class="form-control" role="select2">
                            <option value="">-- Select Client Account --</option>
                            @foreach($clients as $client)
                                @if(old('client_id') == $client->id)
                                    <option value="{{ $client->id }}" selected>{{ $client->getDisplayName() }}</option>
                                @else
                                    <option value="{{ $client->id }}">{{ $client->getDisplayName() }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group {{ has_error('ledger_id') }}">
                        <label for="ledger_id" class="control-label block">Ledger Account</label>
                        <select name="ledger_id" id="ledger_id" class="form-control" role="select2">
                            <option value="">-- Select Ledger --</option>
                            @foreach($ledgers as $ledger)
                                @if(old('ledger_id') == $ledger->id)
                                    <option value="{{ $ledger->id }}" selected>{{ $ledger->name }}</option>
                                @else
                                    <option value="{{ $ledger->id }}">{{ $ledger->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group {{ has_error('cr') }}">
                                <label for="cr" class="control-label">Amount <span>*</span></label>
                                <input type="text" class="form-control" name="cr" placeholder="0" value="{{ old('cr') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ has_error('value_date') }}">
                                <label for="value_date" class="control-label">Value date</label>
                                <input type="text" class="form-control" name="value_date" role="datepicker"
                                       data-date-format="dd-mm-yyyy" value="{{ old('value_date', Carbon\Carbon::now()) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group {{ has_error('narration') }}">
                        <label for="narration" class="control-label">Narration</label>
                        <textarea class="form-control" name="narration" rows="4"
                                  placeholder="Enter a brief narration">{{ old('narration') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Add deposit
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
