<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/11/2016
 * Time: 17:01
 */
?>
@extends('layouts.app')
@section('title', 'Suspend user')
@section('content')
    <form action="{{ route('users.update', compact('user')) }}" method="POST">
        <div class="modal-body text-center">
            <p><i class="fa fa-exclamation-triangle fa-5x text-warning"></i></p>

            <p>If you proceed, <strong>{{ $user->getFullName() }}</strong> won't be able to log into
                the application again until the account is re-activated.
            </p>

            {{ csrf_field() }}
            <input type="hidden" name="_method" value="put">
            <input type="hidden" name="is_active" value="0">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" onclick="javascript:history.back()">Cancel</button>
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-lock"></i> Suspend <strong>{{ $user->getFullName() }}</strong>
            </button>
        </div>
    </form>
@endsection
