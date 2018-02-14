@extends('layouts.app')
@section('title', $title)
@section('page-description')
    Fill out the form below and submit for a loan facility
@endsection
@section('page-actions')
    <button class="btn btn-success js-submit-form" type="submit">
        <i class="fa fa-send"></i> Submit Application
    </button>
@endsection
@section('content')
    <form action="{{ $loan->id ? route('loans.update', compact('loan')) : route('loans.store') }}"
          method="POST" role="form">
        {{ csrf_field() }}

        @if(request()->segment(3) === 'restructure')
            <input type="hidden" value="1" name="restructure">
        @endif

        @if($loan->id)
            <input type="hidden" value="put" name="_method">
        @endif

        <div class="col-md-12 m-b-md">
            @include('dashboard.partials.loans._create_nav_tabs')
            <div class="tab-content m-t">
                @include('dashboard.partials.loans._create_basic_details')
                @include('dashboard.partials.loans._create_loan_fees')
                @include('dashboard.partials.loans._create_guarantors')
                @include('dashboard.partials.loans._create_collaterals')
                @include('dashboard.partials.loans._create_loan_documents')
            </div>
        </div>
    </form>

@push('more_scripts')
    <script type="text/javascript" src="{{ asset('js/loans.js') }}"></script>
@endpush
@endsection