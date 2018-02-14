<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 21:38
 */
?>
<div class="panel tab-pane" id="documents">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4 col-md-offset-4">
                <div class="text-center">
                    <i class="fa fa-file-pdf-o fa-5x m text-danger"></i>
                    <p class="m-b">You can attach support documents for this loan application </p>

                    {{--<input type="file" name="attachments[]" multiple="" style="margin-left: 4em" class="m-b">--}}
                    <em class="text-danger">This functionality is currently unavailable</em>
                </div>
            </div>
        </div>
    </div>
</div>
@push('more_scripts')
<script type="text/javascript" src="{{ asset('libs/dropzone/dist/min/dropzone.min.js') }}"></script>
@endpush
@push('more_styles')
<link rel="stylesheet" href="{{ asset('libs/dropzone/dist/min/dropzone.min.css') }}">
@endpush