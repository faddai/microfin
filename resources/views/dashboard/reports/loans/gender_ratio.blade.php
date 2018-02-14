<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 21/05/2017
 * Time: 20:08
 */
?>
@section('content')
    <div class="panel panel-default">
        <div class="panel-body">

            <div class="table-responsive">

                <table class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        @foreach($report->shift() as $tableHeader)
                            <th>{{ $tableHeader }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @if($report->count())
                        @foreach($report as $key => $_report)
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{{ number_format($_report, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="1">&nbsp;</td>
                            <td>{{ number_format($report->sum(), 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="10">
                                <h5 class="text-center">There is nothing here matching your search criteria.</h5>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
