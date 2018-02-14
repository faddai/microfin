<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/05/2017
 * Time: 20:38
 */
?>
<div class="col-md-2">
    <div class="form-group">
        <input type="text" class="form-control" role="datepicker" name="date" data-date-format="dd/mm/yyyy"
               value="{{ \Carbon\Carbon::parse(request('date'))->format('d/m/Y') }}">
    </div>
</div>
