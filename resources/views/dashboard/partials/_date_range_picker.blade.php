<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/01/2017
 * Time: 11:44 PM
 */
?>
<div class="col-md-3">
    <div class="form-group">
        <div class="form-control pull-right" role="daterangepicker"
             style="background: #fff; cursor: pointer;padding: 5px 10px; border: 1px solid #ccc; width: 100%">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <b class="caret"></b>
        </div>
    </div>
</div>

<input type="hidden" id="start-date" name="startDate" value="{{ request('startDate', Carbon\Carbon::today()) }}">
<input type="hidden" id="end-date" name="endDate" value="{{ request('endDate', Carbon\Carbon::today()) }}">