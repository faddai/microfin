<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 19:20
 */
?>
@extends('layouts.app')
@section('page-actions')
    <a href="#" class="btn btn-danger"><i class="fa fa-trash-o"></i> Delete Client</a>
    <a href="{{ route('clients.edit', compact('client')) }}" class="btn btn-default"><i class="fa fa-pencil"></i> Edit Client</a>
@endsection

@section('content')
    <div class="row">
        @include('dashboard.clients._show_client_photo_and_acount_balance')
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <ul class="nav nav-tabs m-b-n-xxs bg-light">
                        <li class="active">
                            <a href="#profile" data-toggle="tab"><i class="fa fa-user"></i> Profile</a>
                        </li>
                        <li>
                            <a href="#transactions" data-toggle="tab" class="m-l">
                                <i class="fa fa-list"></i> Transactions History
                            </a>
                        </li>
                        <li>
                            <a href="#loans" data-toggle="tab" class="m-l">
                                <i class="fa fa-balance-scale"></i> Loans ({{ $client->loans->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content m-t">

                        <!-- Profile page-->
                        <div class="panel tab-pane active" id="profile">
                            <div class="row m-b">

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">First Name</small>
                                    <div class="font-bold">{{ $client->clientable->firstname }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Middle Name</small>
                                    <div class="font-bold">{{ $client->clientable->middlename or 'N/A'}}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Last Name</small>
                                    <div class="font-bold">{{ $client->clientable->lastname }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Account Number</small>
                                    <div class="font-bold">{{ $client->account_number or 'N/A' }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Branch</small>
                                    <div class="font-bold">{{ $client->branch->name or 'N/A' }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Relationship Manager</small>
                                    <div class="font-bold">{{ $client->relationshipManager->name or 'N/A' }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Email</small>
                                    <div class="font-bold">{{ $client->email or 'N/A'}}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Contact Number</small>
                                    <div class="font-bold">{{ $client->phone1 or 'N/A' }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Gender</small>
                                    <div class="font-bold">{{ ucfirst($client->clientable->gender) }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Address</small>
                                    <div class="font-bold">{{ $client->address or 'N/A' }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Date of Birth</small>
                                    <div class="font-bold">{{ $client->clientable && $client->clientable->dob? $client->clientable->dob->format('F d, Y') : 'N/A' }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">Nationality</small>
                                    <div class="font-bold">{{ $client->country->nationality or 'N/A' }}</div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">ID Type</small>
                                    <div class="font-bold">
                                        {{ $client->identification_type ? trans("identification_types.{$client->identification_type}") : 'N/A' }}
                                    </div>
                                </div>

                                <div class="col-md-3 m-b">
                                    <small class="text-primary">ID No.</small>
                                    <div class="font-bold">{{ $client->identification_number or 'N/A' }}</div>
                                </div>

                            </div>
                        </div>

                        <div class="panel tab-pane" id="transactions">
                            @include('dashboard.clients._show_client_transactions')
                        </div>

                        <div class="panel tab-pane" id="loans">
                            @include('dashboard.clients._show_loans')
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection