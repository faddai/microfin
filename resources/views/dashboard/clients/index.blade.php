<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 16/10/2016
 * Time: 12:12
 */

$term = request()->get('q');
?>
@extends('layouts.app')
@section('title', 'Clients')
@section('page-actions')
    <a href="{{ route('clients.create') }}" class="btn btn-info"><i class="fa fa-user-plus"></i> Create a new Client</a>
@endsection
@section('content')
    <div class="row m-b">
        <form action="{{ route('clients.index') }}" class="form-horizontal">
            <div class="col-md-12">
                <h4>Search for a Client using Account #</h4>
                <div class="form-group">
                    <div class="col-md-8">
                        <input type="text" class="form-control input-lg" name="q" value="{{ old('q', $term) }}"
                               placeholder="Enter client's Account #" autofocus>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fa fa-search"></i> Search
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="row m-b-lg">
        <div class="col-md-12">
            @if(request()->has('q'))
                @if($clients->count())
                    <h4 class="m-b-lg">Search results for: {{ $term }}</h4>
                    @foreach($clients as $client)
                        <div class="col-md-4">
                            <div class="media m-b">
                                <a class="media-left" href="{{ route('clients.show', compact('client')) }}">
                                    <img class="media-object" src="{{ $client->getProfilePhoto() }}"
                                         alt="{{ $client->getFullname() ?: 'N/A' }}"
                                         width="80px">
                                </a>
                                <div class="media-body">
                                    <h4 class="media-heading">
                                        <a href="{{ route('clients.show', compact('client')) }}">
                                            {{ $client->getFullname() ?: 'N/A' }}
                                        </a>
                                    </h4>
                                    <strong>Account #:</strong> {{ $client->account_number }}<br>
                                    <strong>Opened on:</strong> {{ $client->created_at->format('d/m/Y') }}<br>
                                    <strong>By:</strong> {{ $client->createdBy ? $client->createdBy->name : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <h4>No client was found matching your search</h4>
                @endif
            @endif

        </div>
    </div>
@endsection

