<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 21/03/2017
 * Time: 00:20
 */
?>
<div class="row">
    <div class="col-md-12">
        <div class="well">
            <div id="custom-search-input">
                <form action="{{ route('loans.search') }}">
                    <input type="hidden" name="status" value="{{ request('status') ?? request()->segment(2, 'pending') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="credit_officer" class="form-control">
                                <option value="">-- Select Credit Officer --</option>
                                @foreach(\App\Entities\User::creditOfficers() as $creditOfficer)
                                    <option value="{{ $creditOfficer->id }}" {{ request('credit_officer') == $creditOfficer->id ? 'selected' : '' }}>
                                        {{ $creditOfficer->getFullName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="product_id" class="form-control">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->getDisplayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="term"
                                   placeholder="Client/Loan number, name" value="{{ request('term') }}">
                        </div>

                        @include('dashboard.partials._date_range_picker')
                        <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> filter</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
