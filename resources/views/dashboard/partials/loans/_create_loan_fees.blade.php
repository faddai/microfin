<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 21:12
 */
?>
<div class="panel tab-pane" id="loan-fees">
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-condensed" id="js-loan-fees">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th width="100px">Is Upfront</th>
                        <th>
                            Value
                            @include('dashboard.partials._help_tooltip', ['message' => 'Depending on the type of fee,
                            this could either be a percentage of the loan amount or a fixed amount'])
                        </th>
                        <th>
                            Amount ({{ config('app.currency') }})
                            @include('dashboard.partials._help_tooltip', ['message' => 'The actual fee amount to be paid'])
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($fees as $fee)
                        <tr>
                            <input type="hidden" name="fees[{{ $fee->id }}][id]" value="{{ $fee->id }}">

                            <td>{{ $fee->name }}</td>
                            <td>
                                <input type="checkbox" name="fees[{{ $fee->id }}][is_paid_upfront]"
                                        {{ old("fees.{$fee->id}.is_paid_upfront", $fee->isPaidUpfront()) ? 'checked' : '' }}>
                            </td>
                            <td>
                            @if($fee->type === App\Entities\Fee::FIXED)
                                <div class="input-group input-group-sm" style="width: 150px">
                                    <span class="input-group-addon">{{ config('app.currency') }}</span>
                                    <input type="number" class="form-control js-fee-fixed" aria-label="Rate as a fixed amount"
                                           value="{{ old("fees.{$fee->id}.rate", $fee->rate) }}" name="fees[{{ $fee->id }}][rate]">
                                </div>
                            @else
                                <div class="input-group input-group-sm" style="width: 150px">
                                    <input type="number" class="form-control js-fee" aria-label="Rate in percentage"
                                           value="{{ old("fees.{$fee->id}.rate", $fee->rate) }}" name="fees[{{ $fee->id }}][rate]">
                                    <span class="input-group-addon">%</span>
                                </div>
                            @endif
                            </td>
                            <td class="js-computed-fee-amount">0</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="col-md-4 col-md-offset-6 m-t-n text-center text-success">
                    <strong>Total fees</strong>
                    <h2 class="m-t-sm">
                        {{ config('app.currency') }}<span id="js-computed-fee-total-amount"></span>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
