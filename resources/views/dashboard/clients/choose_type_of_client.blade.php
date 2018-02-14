<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/11/2016
 * Time: 2:03 PM
 */
?>
@extends('layouts.app')
@section('title', 'Choose Account type')
@section('content')
    <div class="col-md-6 col-md-offset-3 m-b-lg text-center">
        <div class="row">
            <div class="col-md-12">
                <h4>Choose the type of Account being opened for the Client</h4>
            </div>
        </div>

        <div class="row m-t-lg">
            <div class="col-md-6">
                <a href="{{ route('clients.create', ['type' => 'individual']) }}" class="btn btn-default btn-lg">
                    <i class="fa fa-user"></i> Individual Account
                </a>
            </div>

            <div class="col-md-6">
                <a href="{{ route('clients.create', ['type' => 'corporate']) }}" class="btn btn-default btn-lg">
                    <i class="fa fa-institution"></i> Corporate/SME Account
                </a>
            </div>
        </div>
    </div>
@endsection
