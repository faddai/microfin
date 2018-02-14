<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/02/2017
 * Time: 14:24
 */
$currentRate = $chargedRate = $chargedAmount = 0;
?>
<div class="panel tab-pane" id="loan-fees">
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Current Rate (%)</th>
                <th>Charged Rate (%)</th>
                <th>Charged Amount ({{ config('app.currency') }})</th>
                <th>Is Upfront</th>
            </tr>
            </thead>
            <tbody>
            @if($loan->fees->count())
                @foreach($loan->fees as $fee)
                    <?php $rate = $fee->isFixed() ? round(($fee->rate / $loan->amount) * 100, 2) : $fee->rate ?>
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $fee->name }}</td>
                        <td>{{ $fee->isFixed() ? '-' : $rate }}</td>
                        <td>{{ $fee->pivot->rate }}</td>
                        <td>{{ number_format($fee->pivot->amount, 2) }}</td>
                        <td><i class="fa fa-{{ $fee->pivot->is_paid_upfront ? 'check text-success' : 'close text-danger' }}"></i></td>
                    </tr>
                    <?php
                        $currentRate += $rate;
                        $chargedRate += $fee->pivot->rate;
                        $chargedAmount += $fee->pivot->amount;
                    ?>
                @endforeach
                <tr style="font-weight: bolder">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>{{ $currentRate }}</td>
                    <td>{{ $chargedRate }}</td>
                    <td>{{ number_format($chargedAmount, 2) }}</td>
                    <td>&nbsp;</td>
                </tr>
            @else
                <tr>
                    <td colspan="6">
                        <h5 class="text-center">No fees have been applied to this loan</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>