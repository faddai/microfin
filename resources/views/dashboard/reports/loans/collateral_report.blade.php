<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 21/05/2017
 * Time: 23:09
 */
?>
@section('content')
    <div class="panel panel-default">
        <div class="panel-body">

            <div class="table-responsive">

                <table class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        <th>#</th>
                        @foreach($report->shift() as $tableHeader)
                            <th>{{ $tableHeader }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @if($report->count())
                        @foreach($report as $key => $_report)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td width="20%">
                                    <a href="{{ route('clients.show', ['client' => $_report->get('client')->get('id')]) }}">
                                        {{ $_report->get('client')->get('name') }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('loans.show', ['loan' => $_report->get('loan')->get('id')]) }}">
                                        {{ $_report->get('loan')->get('number') }}
                                    </a>
                                </td>
                                <td>{{ $_report->get('loan')->get('product') }}</td>
                                <td>{{ $_report->get('loan')->get('type') }}</td>
                                <td>{{ $_report->get('loan')->get('disbursed_date') }}</td>
                                <td>{{ $_report->get('loan')->get('amount') }}</td>
                                <td>{{ $_report->get('collateral_type') }}</td>
                                <td>{{ $_report->get('collateral_value') }}</td>
                                <td>{{ $_report->get('percentage_coverage') }}</td>
                            </tr>
                        @endforeach
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
