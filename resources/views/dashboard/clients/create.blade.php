<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 16/10/2016
 * Time: 13:32
 */
$isUpdate = request()->route()->hasParameter('client');
$submitButtonText = $isUpdate ? 'Save changes' : 'Save Client';
$formAction = $isUpdate ? route('clients.update', compact('client')) : route('clients.store');
?>
@extends('layouts.app')
@section('title', $pageTitle)
@section('page-actions')
    <div class="form-group">
        <button class="btn btn-success js-submit-form">
            <i class="fa fa-save"></i> {{ $submitButtonText }}
        </button>
    </div>
@endsection
@section('content')
    <div class="panel-body">
        <form class="form-horizontal" enctype="multipart/form-data" action="{{ $formAction }}" method="POST" role="form">
            {{ csrf_field() }}
            <input type="hidden" name="type" value="{{ $typeOfClient }}">
            @if($isUpdate)
                <input type="hidden" name="_method" value="put">
            @endif
            @include("dashboard.partials._create_{$typeOfClient}_client")
        </form>

    </div>
@endsection


