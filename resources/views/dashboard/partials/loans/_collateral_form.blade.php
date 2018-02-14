<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 19/11/2016
 * Time: 10:03 PM
 */
?>
<div class="collateral-container" data-row-id="{{$index}}">
    
    {!! $index > 0 ? '<hr />' : '' !!}

    <div class="form-group pull-in clearfix">
        @if ($collateral && $collateral->id)
            <input type="hidden" name="collaterals[{{$index}}][collateral_id]" value="{{$collateral->id}}">
        @endif
        <div class="col-md-5 m-b {{ has_error("collaterals.{$index}.label") }}">
            <label>Title</label>
            <input type="text" class="form-control {{ has_error("collaterals.{$index}.label") }}" name="collaterals[{{$index}}][label]"
                   value="{{old("collaterals.{$index}.label", $collateral ? $collateral->label : '')}}">
            <!-- Validation errors -->
            @if (has_error("collaterals.{$index}.label"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("collaterals.{$index}.label") }}</strong></span>
            @endif
        </div>

        <div class="col-md-5 {{ has_error("collaterals.{$index}.market_value") }}">
            <label>Value</label>
            <input type="number" class="form-control" name="collaterals[{{$index}}][market_value]"
                   value="{{old("collaterals.{$index}.market_value", $collateral ? $collateral->market_value : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("collaterals.{$index}.market_value"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("collaterals.{$index}.market_value") }}</strong></span>
            @endif
        </div>
        @if ($index == 0)
            <div class="col-md-2 m-b">
                <button type="button" class="btn btn-info" id="add-collateral" style="margin-top:24px">
                    <i class="fa fa-plus"></i> Add another
                </button>
            </div>
        @else
            <div class="col-md-2 m-b">
                <button type="button" class="btn btn-danger remove-collateral" style="margin-top:24px">Remove collateral
                </button>
            </div>
        @endif
    </div>
</div>

<script type="text/x-custom-template" id="collateral-template">
    <div class="collateral-container">
        <hr/>
        <div class="form-group pull-in clearfix">
            <div class="col-md-5 m-b">
                <label>Title</label>
                <input type="text" class="form-control collateral-title">
            </div>
            <div class="col-md-5">
                <label>Value</label>
                <input type="text" class="form-control collateral-value">
            </div>
            <div class="col-md-2 m-b">
                <button type="button" class="btn btn-danger remove-collateral" style="margin-top:24px">Delete
                    Collateral
                </button>
            </div>
        </div>
    </div>
</script>

