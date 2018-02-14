<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/04/2017
 * Time: 00:19
 */
?>
@section('title', $report->get('title', 'CRB Monthly Report'))
@section('page-description', '')
@section('content-filter')
    <div id="custom-search-input">
        <form action="">
            <div class="row">
                <div class="col-md-3">
                    <select name="client_type" class="form-control">
                        <option value="">-- Select Client Type --</option>
                        @foreach(['Individual', 'Corporate'] as $clientable)
                            <option value="Morph{{ $clientable }}" {{ str_contains(request('client_type'), $clientable) ? 'selected' : '' }}>{{ $clientable }}</option>
                        @endforeach
                    </select>
                </div>

                @include('dashboard.partials._datepicker')

                <button class="btn btn-primary" type="submit" name="export" value="true" disabled>
                    <i class="fa fa-download"></i> Download Report
                </button>
            </div>
        </form>
    </div>
    @push('more_scripts')
    <script type="text/javascript">
        $(function () {
            $('select[name="client_type"]').change(function (evt) {
                var selected = $(this).val();

                if (selected === '') {
                    $('button[name="export"]').attr('disabled', 'disabled');
                } else {
                    $('button[name="export"]').removeAttr('disabled');
                }
            })
        })
    </script>
    @endpush
@endsection