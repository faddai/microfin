<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/02/2017
 * Time: 14:27
 */
$cumInterest = $cumPrincipal = $cumPaidPrincipal = $cumPaidInterest = $cumFees = $cumPaidFees = $cumOutstanding = 0
?>
<div class="panel tab-pane active" id="schedule">
    <div class="row hidden-print m">
        @include('dashboard.partials._data_export_buttons', ['links' => show_export_links('loans.schedule.download', compact('loan'))])
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Principal</th>
                <th>Paid principal</th>
                <th>Interest</th>
                <th>Paid interest</th>
                <th>Fees</th>
                <th>Paid Fees</th>
                <th>Outstanding</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @if($loan->schedule->count())
                @foreach($loan->schedule as $schedule)
                    <tr class="bg bg-{{ $schedule->getStatus()->get('background') }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $schedule->due_date ? $schedule->due_date->format(config('microfin.dateFormat')) : 'N/A' }}</td>
                        <td>{{ $schedule->getPrincipal() }}</td>
                        <td>{{ $schedule->getPaidPrincipal() }}</td>
                        <td>{{ $schedule->getInterest() }}</td>
                        <td>{{ $schedule->getPaidInterest() }}</td>
                        <td>{{ $schedule->getFees() }}</td>
                        <td>{{ $schedule->getPaidFees() }}</td>
                        <td>{{ $schedule->getOutstandingRepaymentAmount() }}</td>
                        <td>{!! $schedule->getStatus()->get('label') !!}</td>
                    </tr>
                    <?php
                        $cumPrincipal += $schedule->getPrincipal(false);
                        $cumInterest += $schedule->getInterest(false);
                        $cumPaidPrincipal += $schedule->getPaidPrincipal(false);
                        $cumPaidInterest += $schedule->getPaidInterest(false);
                        $cumPaidFees += $schedule->getPaidFees(false);
                        $cumFees += $schedule->getFees(false);
                        $cumOutstanding += $schedule->getOutstandingRepaymentAmount(false);
                    ?>
                @endforeach
                <tr style="font-weight: bolder; border-top-width: 1px">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>{{ number_format($cumPrincipal, 2) }}</td>
                    <td>{{ number_format($cumPaidPrincipal, 2) }}</td>
                    <td>{{ number_format($cumInterest, 2) }}</td>
                    <td>{{ number_format($cumPaidInterest, 2) }}</td>
                    <td>{{ number_format($cumFees, 2) }}</td>
                    <td>{{ number_format($cumPaidFees, 2) }}</td>
                    <td>{{ number_format($cumOutstanding, 2) }}</td>
                    <td>&nbsp;</td>
                </tr>
            @else
                <tr>
                    <td colspan="11" class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x block"></i>
                        <h5>Hold on, we are currently generating the repayment schedule for this loan.</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>