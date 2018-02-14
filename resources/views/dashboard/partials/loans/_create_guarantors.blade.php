<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 21:23
 */
?>
<div class="panel tab-pane" id="guarantors">
    <div class="row">
        <div class="col-md-12">

            <div class="col-md-12">
                <p>Please provide Guarantor details below;</p>
            </div>

             @if(count(old('guarantors', [])))
                @foreach(old('guarantors', []) as $i => $row)
                    @include('dashboard.partials.loans._guarantor_form', [
                        'index' => $i,
                        'guarantor' => $loan->guarantors->count() > $i ? $loan->guarantors[$i] : null
                    ])
                @endforeach
            @elseif ($loan->guarantors->count())
                @foreach($loan->guarantors as $i => $row)
                    @include('dashboard.partials.loans._guarantor_form', [
                        'index' => $i,
                        'guarantor' => $loan->guarantors[$i]
                    ])
                @endforeach
            @else
                @include('dashboard.partials.loans._guarantor_form', ['index' => 0, 'guarantor' => null])
            @endif
        </div>
    </div>
</div>
