<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 09:32
 */
$links = $links ?? collect();
?>
<div class="col-md-6 pull-right m-b">
    <div class="btn-group btn-group-sm pull-right" role="group" aria-label="Data Export Buttons">
        <a href="{{ $links->get('print', '#') }}" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        <a href="{{ $links->get('pdf', '#') }}" class="btn btn-default"><i class="fa fa-file-pdf-o text-danger"></i> Download</a>
        <a href="{{ $links->get('csv', '#') }}" class="btn btn-default"><i class="fa fa-file-excel-o text-info"></i> Export</a>
    </div>
</div>