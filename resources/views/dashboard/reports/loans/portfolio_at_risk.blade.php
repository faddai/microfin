<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/04/2017
 * Time: 06:34
 */
?>
@section('content-filter')
    <div id="custom-search-input">
        <form action="">
            <div class="row">
                <div class="col-md-2">
                    <select name="no_of_days" class="form-control">
                        <option value="1">PAR >1</option>
                        @foreach([30, 90, 120] as $par)
                            <option value="{{ $par }}" {{ request('no_of_days', 120) == $par ? 'selected' : '' }}>
                                {{ sprintf('PAR >%d', $par) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="credit_officer" class="form-control">
                        <option value="">All Credit Officers</option>
                        @foreach($creditOfficers as $creditOfficer)
                            <option value="{{ $creditOfficer->id }}"
                                    {{ request('credit_officer') == $creditOfficer->id ? 'selected' : '' }}>
                                {{ $creditOfficer->getFullName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="product_id" class="form-control">
                        <option value="">All loan products</option>
                        @foreach(cache('products') as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="loan_type" class="form-control">
                        <option value="">All loan types</option>
                        @foreach($loanTypes as $type)
                            <option value="{{ $type->id }}" {{ request('loan_type') == $type->id ? 'selected' : '' }}>
                                {{ $type->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @include('dashboard.partials._datepicker')

                <div class="col-md-1">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> filter</button>
                </div>

                {{--<div class="col-md-3">--}}
                    {{--<div class="btn-group btn-group-sm pull-right" role="group" aria-label="Data Export Buttons">--}}
                        {{--<a href="#" class="btn btn-default"><i class="fa fa-print"></i> Print</a>--}}
                        {{--<a href="#" class="btn btn-default"><i class="fa fa-file-pdf-o text-danger"></i> Download</a>--}}
                        {{--<a href="#" class="btn btn-default"><i class="fa fa-file-excel-o text-info"></i> Export</a>--}}
                    {{--</div>--}}
                {{--</div>--}}
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
                    @foreach($report->shift() as $tableHeader)
                        <th>{{ $tableHeader }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @if($report->count())
                    @foreach($report as $_report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('loans.show', ['loan' => $_report->get('loan')->get('id')]) }}">
                                {{ $_report->get('loan')->get('number') }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('clients.show', ['client' => $_report->get('client')->get('id')]) }}">
                                {{ $_report->get('client')->get('name') }}
                                </a>
                            </td>
                            <td>{{ $_report->get('loan')->get('credit_officer') }}</td>
                            <td>{{ $_report->get('loan')->get('amount') }}</td>
                            <td>{{ $_report->get('principal_due') }}</td>
                            <td>{{ $_report->get('interest_due') }}</td>
                            <td>{{ $_report->get('p+i_due') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4">&nbsp;</td>
                        <td>{{ number_format($report->totals->get('loan_amount'), 2) }}</td>
                        <td>{{ number_format($report->totals->get('principal_due'), 2) }}</td>
                        <td>{{ number_format($report->totals->get('interest_due'), 2) }}</td>
                        <td>{{ number_format($report->totals->get('amount_due'), 2) }}</td>
                        <td colspan="4">&nbsp;</td>
                    </tr>

                    <tr>
                        <td colspan="10">&nbsp;</td>
                    </tr>

                    <tr>
                        <td colspan="4">&nbsp;</td>
                        <td>PAR >{{ request('no_of_days', 1) }}</td>
                        <td>{{ number_format($report->par, 2) }}%</td>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="10">
                            <h5 class="text-center">There are no portfolios matching your search criteria.</h5>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
