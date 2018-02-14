<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 21:27
 */
?>
<div class="panel tab-pane" id="collaterals">

    <div class="row">
        <div class="col-md-12">

            <div class="col-md-12">
                <p>Please provide Collateral details below;</p>
            </div>

            @if(count(old('collaterals', [])))
                @foreach(old('collaterals', []) as $i => $row)
                    @include('dashboard.partials.loans._collateral_form', [
                        'index' => $i,
                        'collateral' => $loan->collaterals()->count() > $i ? $loan->collaterals[$i] : null
                    ])
                @endforeach
            @elseif ($loan->collaterals->count())
                @foreach($loan->collaterals as $i => $row)
                    @include('dashboard.partials.loans._collateral_form', [
                        'index' => $i,
                        'collateral' => $loan->collaterals[$i]
                    ])
                @endforeach
            @else
                @include('dashboard.partials.loans._collateral_form', ['index' => 0, 'collateral' => null])
            @endif
        </div>
    </div>
</div>
