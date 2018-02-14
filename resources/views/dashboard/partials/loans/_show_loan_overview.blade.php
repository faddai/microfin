<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/02/2017
 * Time: 21:37
 */
?>
<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Rpmt Start Date</th>
            <th>Maturity</th>
            <th>Repayment</th>
            <th>Principal</th>
            <th>Interest</th>
            <th>Fees</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $loan->schedule->count() ? $loan->schedule->first()->due_date->format(config('microfin.dateFormat')) : 'n/a' }}</td>
            <td>{{ $loan->maturity_date ? $loan->maturity_date->format(config('microfin.dateFormat')) : 'n/a' }}</td>
            <td>{{ $loan->tenure->label }} x {{ $loan->repaymentPlan->label }}</td>
            <td>{{ $loan->getPrincipalAmount() }} @ {{ number_format($loan->rate, 2) }}%</td>
            <td>{{ number_format($loan->schedule->sum('interest'), 2) }}</td>
            <td>{{ $loan->getTotalFees() }}</td>
            <td>{{ $loan->getTotalLoanAmount() }}</td>
            <td>{{ $loan->getAmountPaid() }}</td>
            <td>{{ $loan->getBalance() }}</td>
            <td>@include('dashboard.partials.loans._status_label')</td>
        </tr>
        </tbody>
    </table>

</div>
