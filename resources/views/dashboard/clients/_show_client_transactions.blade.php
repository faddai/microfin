<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 04:24
 */
$balance = 0;
?>
<div class="table-responsive">
    <table class="table">
        @include('dashboard.partials._data_export_buttons', ['links' => show_export_links('clients.transactions.download', compact('client'))])
        <thead>
        <tr>
            <th>#</th>
            <th>Txn Date</th>
            <th>Value Date</th>
            <th>Narration</th>
            <th>Dr</th>
            <th>Cr</th>
            <th>Balance</th>
        </tr>
        </thead>
        <tbody>
        @if($client->transactions->count())
            @foreach($client->transactions as $transaction)
                <?php $balance += $transaction->cr > 0 ? $transaction->cr : $transaction->dr * -1 ?>
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $transaction->created_at->format(config('microfin.dateFormat')) }}</td>
                    <td>{{ $transaction->value_date ? $transaction->value_date->format(config('microfin.dateFormat')) : 'n/a' }}</td>
                    <td>{{ $transaction->narration }}</td>
                    <td>{{ number_format($transaction->dr, 2) }}</td>
                    <td>{{ number_format($transaction->cr, 2) }}</td>
                    <td>{{ number_format(abs($balance), 2) }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6">
                    <h5 class="text-center">There are no transactions here currently.</h5>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>