<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 02/03/2017
 * Time: 19:38
 */
$loanAmount = config('app.currency') .''. number_format($loan->amount, 2);
$message = 'Select the date on which you\'d like the disbursement to take effect, if you don\'t specify, today\'s date will be used.';
?>
<div id="disburse-loan-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">You're about to Disburse a Loan amount of {{ $loanAmount }}</h4>
            </div>

            <form action="{{ route('loans.disburse', compact('loan')) }}" method="POST">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Note: </strong> Once you disburse the Loan, it can't be cancelled.
                    </div>

                    <div class="well">
                        @if($loan->getTotalFees(false) === 0.0)
                            <p>This loan has no fees applied</p>
                        @else
                            <h5>Fees charged ({{ config('app.currency').''. $loan->getTotalFees() }})</h5>
                            <ul>
                            @foreach($loan->fees as $fee)
                                <li>
                                    <strong>
                                        {{ $fee->name }} = {{ config('app.currency').''. number_format($fee->pivot->amount, 2) }} ({{ $fee->pivot->rate }}% of Loan amount)
                                        @if($fee->pivot->is_paid_upfront)<span class="label label-danger text-uppercase">Paid upfront</span>@endif
                                    </strong>
                                </li>
                            @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="disbursed_at" class="control-label">
                            Disbursement Date <small>(DD-MM-YYYY)</small>
                            @include('dashboard.partials._help_tooltip', compact('message'))
                        </label>
                        <input type="text" id="disbursed_at" class="form-control" data-date-format="dd-mm-yyyy"
                               value="{{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}">
                        <input type="hidden"  name="disbursed_at">
                    </div>

                    @if($loan->client->canReceiveEmailNotification())
                        <div class="form-group">
                            <label for="notify-client" class="control-label">
                            <input type="checkbox" id="notify-client" name="notify_client_of_disbursement" checked>
                                Notify <strong>{{ $loan->client->getFullName() }}</strong> of the disbursement
                            </label>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="disbursal-remarks" class="control-label">Remarks</label>
                        <textarea id="disbursal-remarks" class="form-control" name="disbursal_remarks" rows="4"
                                  placeholder="Add remarks, comment, etc">{{ $loan->disbursal_remarks }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check-square-o"></i>
                        Disburse Loan Amount of {{ $loanAmount }}
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
@push('more_scripts')
<script type="text/javascript">

    $('input[name="disbursed_at"]').val($('#disbursed_at').val());

    $('#disbursed_at').datepicker()
            .on('changeDate', function (e) {

                $('input[name="disbursed_at"]').val(moment(e.date).format('YYYY-MM-DD'));

                $(this).datepicker('hide')
            })
</script>
@endpush