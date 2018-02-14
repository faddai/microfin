<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/05/2017
 * Time: 20:59
 */
?>
@section('content-filter')
    <div id="custom-search-input">
        <form action="">
            <div class="row">
                <div class="col-md-3">
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
                    <button class="btn btn-primary btn-md" type="submit"><i class="fa fa-filter"></i> filter</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('content')
    <div class="panel panel-default">
        <div class="panel-body">

            <div class="table-responsive">

                <table class="table table-striped">
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
                        @foreach($report as $_report)
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
                                <td>{{ $_report->get('days') }}</td>
                                <td>{{ $_report->get('loan')->get('maturity') }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bolder">
                            <td colspan="6">&nbsp;</td>
                            <td>{{ number_format($report->totals->get('amount'), 2) }}</td>
                            <td colspan="2">&nbsp;</td>
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

