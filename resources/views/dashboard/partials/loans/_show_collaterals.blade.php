<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/02/2017
 * Time: 14:29
 */
?>
<div class="panel tab-pane" id="collaterals">
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Market value</th>
            </tr>
            </thead>
            <tbody>
            @if($loan->collaterals->count())
                @foreach($loan->collaterals as $guarantor)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $guarantor->label or 'n/a' }}</td>
                        <td>{{ $guarantor->market_value or 'n/a' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3">
                        <h5 class="text-center">There are no collaterals for this loan</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>