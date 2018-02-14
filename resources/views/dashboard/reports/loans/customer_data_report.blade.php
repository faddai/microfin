<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 21/05/2017
 * Time: 18:40
 */
?>
@section('content-filter')
    <div id="custom-search-input">
        <form action="">
            <div class="row">
                <div class="col-md-3">
                    <select name="client_type" class="form-control">
                        <option value="">All Client Types</option>
                        @foreach(['Individual', 'Corporate'] as $clientable)
                            <option value="Morph{{ $clientable }}" {{ str_contains(request('client_type'), $clientable) ? 'selected' : '' }}>{{ $clientable }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn btn-primary" type="submit" name="export" value="true">
                    <i class="fa fa-filter"></i> filter
                </button>
            </div>

        </form>
    </div>
@endsection
@section('content')
    <div class="panel panel-default">
        <div class="panel-body">

            <div class="table-responsive">

                <table class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        <th>#</th>
                        @foreach($report->shift() as $tableHeader)
                            <th width="{{ in_array($tableHeader, ['Date of Birth', 'ID Type'], true) ? '10%' : ''}}">
                                {{ $tableHeader }}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @if($report->count())
                        @foreach($report as $_report)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td width="20%">
                                    <a href="{{ route('clients.show', ['client' => $_report->get('id')]) }}">
                                        {{ $_report->get('name') }}
                                    </a>
                                </td>
                                <td>{{ $_report->get('address') }}</td>
                                <td>{{ $_report->get('phone') }}</td>
                                <td>{{ trans('identification_types')[$_report->get('identification_type')] }}</td>
                                <td>{{ $_report->get('identification_number') }}</td>
                                <td>{{ $_report->get('email') !== 'NULL' ? $_report->get('email') : '' }}</td>
                                <td>{{ $_report->get('dob') ? $_report->get('dob')->format(config('microfin.dateFormat')) : '' }}</td>
                                <td>{{ ucfirst($_report->get('gender')) }}</td>
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
