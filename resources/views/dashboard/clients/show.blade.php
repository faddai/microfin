<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 19:20
 */
?>
@extends('layouts.app')
@section('title', 'Client details')
@section('page-actions')
    <a href="#" class="btn btn-danger"><i class="fa fa-trash-o"></i> Delete Client</a>
    <a href="{{ route('clients.edit', compact('client')) }}" class="btn btn-default"><i class="fa fa-pencil"></i> Edit Client</a>
@endsection

@section('content')
    @if($client->isIndividual())
        @include('dashboard.clients._individual')
    @elseif($client->isCorporate())
        @include('dashboard.clients._corporate')
    @endif
@endsection