<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/10/2016
 * Time: 21:14
 */

$defaultFeeReceivableLedgerId = $ledgers->where('code', 6002)->first()->id;
?>
@extends('layouts.app')
@section('title', 'Settings')
@section('content')
    <div class="col-md-2 col-sm-12">
        <nav class="side-menu">
            <ul class="nav tabs">
                <li class="active"><a href="#profile" data-toggle="tab">Profile</a></li>
                <li><a href="#users" data-toggle="tab">Manage Users</a></li>
                <li><a href="#products" data-toggle="tab">Loan Products</a></li>
                <li><a href="#fees" data-toggle="tab">Loan Fees</a></li>
                <li><a href="#branches" data-toggle="tab">Branches</a></li>
                <li><a href="#zones" data-toggle="tab">Zones</a></li>
            </ul>
        </nav>
    </div>

    <!-- tab content -->
    <div class="col-md-10 col-sm-12">
        <div class="tab-content">
            {{-- profile --}}
            <div class="tab-pane active" id="profile">
                <h3 class="help-block">Profile</h3>
                <hr>
                @include('dashboard.partials._profile')
            </div>

            {{-- loan fees --}}
            <div class="tab-pane" id="fees">
                <h3 class="help-block">Loan Fees
                    <small class="pull-right">
                        <a href="#edit-fee-modal" data-toggle="modal" class="btn btn-primary btn-sm"
                           data-is-paid-upfront="false"
                           data-receivable-ledger="{{ $defaultFeeReceivableLedgerId }}"
                           data-url="{{ route('settings.fees.store') }}">
                            <i class="fa fa-plus"></i> Add fee
                        </a>
                    </small>
                </h3>
                <hr>
                @include('dashboard.partials._loan_fees_setup')
            </div>

            {{-- branches --}}
            <div class="tab-pane" id="branches">
                <h3 class="help-block">
                    Branches
                    <small class="pull-right">
                        <a href="#add-branch-modal" data-toggle="modal" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add branch
                        </a>
                    </small>
                </h3>
                <hr>
                @include('dashboard.partials._branches')
            </div>

            {{-- zones --}}
            <div class="tab-pane" id="zones">
                <h3 class="help-block">Zones
                    <small class="pull-right">
                        <a href="#edit-zone-modal" data-toggle="modal" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add zone
                        </a>
                    </small>
                </h3>
                <hr>
                @include('dashboard.partials._zones')
            </div>

            {{-- users --}}
            <div class="tab-pane" id="users">
                <h3 class="help-block">Manage Users
                    <small class="pull-right">
                        <a href="#add-user-modal" data-toggle="modal" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add user
                        </a>
                    </small>
                </h3>
                <hr>
                @include('dashboard.partials._manage_users')
            </div>

            {{-- loan products --}}
            <div class="tab-pane" id="products">
                <h3 class="help-block">Loan Products
                    <small class="pull-right">
                        <a href="#edit-product-modal" data-toggle="modal" class="btn btn-primary btn-sm"
                           data-url="{{ route('settings.products.store') }}">
                            <i class="fa fa-plus"></i> Add Loan Product
                        </a>
                    </small>
                </h3>
                <hr>
                @include('dashboard.partials._loan_products')
            </div>

        </div>
    </div>
@endsection
@push('more_scripts')
    <script src="{{ asset('js/settings.js') }}"></script>
@endpush
