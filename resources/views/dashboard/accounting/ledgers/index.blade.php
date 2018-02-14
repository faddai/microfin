<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 01/03/2017
 * Time: 09:42
 */
?>
@extends('layouts.app')
@section('title', 'G.L. Statement')
@section('page-actions')
    <div class="ledgers-filter m-t-md m-b">
        <form action="{{ route('accounting.ledgers.index', compact('ledger')) }}" class="form-inline">
            <div class="form-group">
                <select name="ledger_id" id="ledger" class="form-control" role="select2" style="width: 200px">
                    <option value="">-- Choose ledger --</option>
                    @foreach($categories as $category)
                        <optgroup label="{{ $category->name }}"></optgroup>
                        @foreach($category->ledgers as $_ledger)
                            @if(request('ledger_id') == $_ledger->id)
                                <option value="{{ $_ledger->id }}" selected>{{ $_ledger->name }}</option>
                            @else
                                <option value="{{ $_ledger->id }}">{{ $_ledger->name }}</option>
                            @endif
                        @endforeach
                    @endforeach
                </select>
            </div>

            @include('dashboard.partials._date_range_picker')

        </form>
    </div>
@endsection