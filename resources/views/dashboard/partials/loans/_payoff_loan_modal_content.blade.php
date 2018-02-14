<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 10/06/2017
 * Time: 09:23
 */

use App\Entities\Accounting\LedgerCategory;

$ledgers = LedgerCategory::getBankOrCashLedgers();
?>
<div id="payoff-loan-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Loan Payoff</h4>
            </div>

            <table class="table table-bordered hide">
                <tbody>
                <tr>
                    <td>Client Name</td>
                    <td id="client">n/a</td>
                </tr>
                <tr>
                    <td>Loan Number</td>
                    <td id="loan-number">n/a</td>
                </tr>
                <tr>
                    <td>Maturity Date</td>
                    <td id="maturity">n/a</td>
                </tr>
                <tr>
                    <td>Request created date</td>
                    <td id="created-at">n/a</td>
                </tr>
                <tr>
                    <td>Request created by</td>
                    <td id="creator">n/a</td>
                </tr>
                </tbody>
            </table>

            <form action="{{ route('loans.payoff.store', compact('loan')) }}" method="POST">
                {{ csrf_field() }}
                <div class="modal-body">

                    <div class="text-center clearfix">
                        <div class="col-md-6 m-b">
                            <small class="text-primary">Loan Balance</small>
                            <div style="font-size: 2em; font-weight: bolder">
                                {{ config('app.currency') }} <span
                                        id="outstanding-loan-amount">{{ $loan->getBalance() }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 m-b">
                            <small class="text-primary">Payoff Amount</small>
                            <div style="font-size: 2em; font-weight: bolder">
                                {{ config('app.currency') }} <span id="payoff-amount">{{ $loan->getBalance() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="principal" class="control-label">Principal</label>
                                <input type="text" id="principal" name="principal" class="form-control"
                                       value="{{ $loan->getOutstanding('principal') }}" placeholder="0.00">
                            </div>

                            <div class="form-group">
                                <label for="interest" class="control-label">Interest</label>
                                <input type="text" id="interest" name="interest" class="form-control" placeholder="0.00"
                                       value="{{ $loan->getOutstanding('interest') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fees" class="control-label">Fees</label>
                                <input type="text" id="fees" name="fees" class="form-control" placeholder="0.00"
                                       value="{{ $loan->getOutstanding('fees') }}">
                            </div>

                            <div class="form-group">
                                <label for="penalty" class="control-label">Penalty</label>
                                <input type="text" id="penalty" name="penalty" class="form-control" value="0.00"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="remarks" class="control-label">Remarks</label>
                        <textarea id="remarks" class="form-control" name="remarks" rows="4"
                                  placeholder="Add remarks, comment, etc"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check-square-o"></i>
                        Submit for Approval
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
@push('more_scripts')
<script type="text/javascript" src="{{ asset('js/payoff.js') }}"></script>
@endpush