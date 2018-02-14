<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/11/2016
 * Time: 10:31 AM
 */
?>
@extends('layouts.app')
@section('title', 'Edit user details')
@section('page-description')
    Update user information
@endsection
@section('page-actions')
    <button type="submit" class="btn btn-primary js-submit-form"><i class="fa fa-save"></i> Save changes</button>
@endsection
@section('content')
    <form action="{{ route('users.update', compact('user')) }}" method="POST" role="form">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="put">
        <div class="form-group">
            <label for="name" class="control-label">Name</label>
            <input type="text" id="name" class="form-control" name="name" placeholder="John Doe"
                   value="{{ old('name', $user->name) }}">
        </div>

        <div class="form-group">
            <label for="email" class="label-control">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="john@betternowfinance.co.zm"
                   value="{{ old('email', $user->email) }}">
        </div>

        <div class="form-group">
            <label for="branch" class="label-control">Branch</label>
            <select class="form-control" id="branch" name="branch_id">
                <option value="">-- Choose branch --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $user->branch ? ($user->branch->id === $branch->id ? 'selected' : '') : '' }}>
                        {{ $branch->name .' ('. $branch->location .') ' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group well">
            <p>Assign role(s) to the user to enable him/her carry out his job functions.</p>
            @foreach($roles as $role)
                @if(in_array($role->id, old('roles', [])) ||
                in_array($role->id, $user->roles->count() ? $user->roles->pluck('id')->toArray() : []))
                    <label for="role_{{ $role->id }}" class="control-label m-r-md">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                               id="role_{{ $role->id }}" checked> {{ $role->display_name }}
                    </label>
                @else
                    <label for="role_{{ $role->id }}" class="control-label m-r-md">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                               id="role_{{ $role->id }}"> {{ $role->display_name }}
                    </label>
                @endif
            @endforeach
        </div>



    </form>

@endsection


