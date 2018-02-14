<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/02/2017
 * Time: 14:31
 */
$balance = 0;
?>
<div class="panel tab-pane" id="loan-statement">
    <div class="row hidden-print m">
        @include('dashboard.partials._data_export_buttons', ['links' => show_export_links('loans.statement.download', compact('loan'))])
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Txn Date</th>
                <th>Value Date</th>
                <th>Narration</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
            </thead>
            <tbody>
            @if($loan->statement)
                @foreach($loan->statement->entries as $entry)
                    <?php $balance += array_sum([$entry->dr * -1, $entry->cr]) ?>
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $entry->created_at->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $entry->value_date->format(config('microfin.dateFormat')) }}</td>
                        <td>{{ $entry->narration }}</td>
                        <td>{{ $entry->getDebitAmount() }}</td>
                        <td>{{ $entry->getCreditAmount() }}</td>
                        <td>{{ number_format($balance, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7">
                        <h5 class="text-center">There is nothing here yet.</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>