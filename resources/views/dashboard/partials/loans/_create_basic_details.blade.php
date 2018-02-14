<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 20:54
 */
?>
<div class="panel tab-pane active" id="basic-details">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group {{ has_error('client_id') }}">
                <label for="client_id" class="label-control">
                    Client Account <span>*</span>
                </label>
                <select name="client_id" id="client_id" class="form-control" role="select2">
                    <option value="">-- Select Client Account --</option>
                    @foreach($clients as $client)
                        @if(old('client_id') == $client->id)
                            <option value="{{ $client->id }}" selected>
                                {{ $client->getDisplayName() }}
                            </option>
                        @else
                            <option value="{{ $client->id }}" {{ $loan->client && ($loan->client->id == $client->id) ? 'selected' : '' }}>
                                {{ $client->getDisplayName() }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ has_error('amount') }}">
                        <label for="amount" class="label-control">Loan Amount <span>*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control" placeholder="Eg. 10000"
                               value="{{ old('amount', $loan->amount) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">&nbsp;</label>
                        <input type="text" class="form-control" value="0.0" id="formatted-amount" data-currency="{{ config('app.currency') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ has_error('tenure_id') }}">
                        <label for="tenure_id" class="label-control">Loan Tenure (months) <span>*</span></label>
                        <select name="tenure_id" id="tenure_id" class="form-control">
                            <option value="">-- Select Loan Tenure --</option>
                            @foreach($tenures as $tenure)
                                @if(old('tenure_id') == $tenure->id || ($loan->tenure && $loan->tenure->id === $tenure->id))
                                    <option value="{{ $tenure->id }}" selected>{{ $tenure->label }}</option>
                                @else
                                    <option value="{{ $tenure->id }}" {{ old('tenure_id') == $tenure->id ? 'selected' : '' }}>
                                        {{ $tenure->label }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ has_error('rate') }}">
                        <label for="rate" class="label-control">Monthly Rate (%) <span>*</span></label>
                        <input type="number" name="rate" id="rate" class="form-control"
                               placeholder="Eg. 5.32" value="{{ old('rate', $loan->rate) }}">
                    </div>
                </div>
            </div>

            <div class="form-group {{ has_error('interest_calculation_strategy') }}">
                <label for="interest_calculation_strategy" class="label-control">Interest Calculation method</label>
                <select name="interest_calculation_strategy" id="interest_calculation_strategy" class="form-control">
                    @foreach($interestCalculationStrategies as $key => $value)
                        <option value="{{ $key }}" {{ $key === 'reducing_balance' ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group {{ has_error('grace_period') }}">
                <label for="grace_period" class="label-control">Grace Period (in days)</label>
                <input type="number" name="grace_period" id="grace_period" class="form-control"
                       placeholder="Eg. 3" value="{{ old('grace_period', $loan->grace_period ?: 0) }}">
            </div>

            <div class="form-group {{ has_error('repayment_plan_id') }}">
                <label for="repayment_plan_id" class="label-control">Repayment Plan <span>*</span></label>
                <select name="repayment_plan_id" id="repayment_plan_id" class="form-control">
                    @foreach($repaymentPlans as $repaymentPlan)
                        @if(old('repayment_plan_id') == $repaymentPlan->id ||
                        ($loan->repaymentPlan && $loan->repaymentPlan->id === $repaymentPlan->id))
                            <option value="{{ $repaymentPlan->id }}" selected>{{ $repaymentPlan->label }}</option>
                        @else
                            <option value="{{ $repaymentPlan->id }}" {{ old('repayment_plan_id') == $repaymentPlan->id ? 'selected' : '' }}>
                                {{ $repaymentPlan->label }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group {{ has_error('credit_officer') }}">
                <label for="credit_officer" class="label-control">Credit Officer <span>*</span></label>
                <select name="credit_officer" id="credit_officer" class="form-control">
                    <option value="">-- Select Credit Officer --</option>
                    @foreach($creditOfficers as $creditOfficer)
                        @if(old('credit_officer') == $creditOfficer->id ||
                        ($loan->creditOfficer && $loan->creditOfficer->id === $creditOfficer->id))
                            <option value="{{ $creditOfficer->id }}" selected>{{ $creditOfficer->name }}</option>
                        @else
                            <option value="{{ $creditOfficer->id }}">
                                {{ $creditOfficer->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6">

            <div class="form-group {{ has_error('loan_product_id') }}">
                <label for="loan_product_id" class="label-control">Loan Product <span>*</span></label>
                <select name="loan_product_id" id="loan_product_id" class="form-control">
                    <option value="">-- Select Loan Product --</option>
                    @foreach($products as $product)
                        @if(old('loan_product_id') == $product->id ||  ($loan->product && $loan->product->id == $product->id))
                            <option value="{{ $product->id }}" selected>{{ $product->getDisplayName() }}</option>
                        @else
                            <option value="{{ $product->id }}">
                                {{ $product->getDisplayName() }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group {{ has_error('loan_type_id') }}">
                <label for="loan_type_id" class="label-control">Loan Type <span>*</span></label>
                <select name="loan_type_id" id="loan_type_id" class="form-control">
                    <option value="">-- Select Loan Type --</option>
                    @foreach($loanTypes as $loanType)
                        @if(old('loan_type_id') == $loanType->id ||  ($loan->type && $loan->type->id == $loanType->id))
                            <option value="{{ $loanType->id }}" selected>{{ $loanType->label }}</option>
                        @else
                            <option value="{{ $loanType->id }}">
                                {{ $loanType->label }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group {{ has_error('zone_id') }}">
                <label for="zone_id" class="label-control">Zone <span>*</span></label>
                <select name="zone_id" id="zone_id" class="form-control">
                    @foreach($zones as $zone)
                        @if(old('zone_id') == $zone->id ||  ($loan->zone && $loan->zone->id === $zone->id))
                            <option value="{{ $zone->id }}" selected>{{ $zone->name }}</option>
                        @else
                            <option value="{{ $zone->id }}" {{ $loan->zone ? ($loan->zone->id == $zone->id ? 'selected' : '') : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group {{ has_error('business_sector_id') }}">
                <label for="business_sector" class="label-control">Business Sector</label>
                <select name="business_sector_id" id="business_sector" class="form-control">
                    <option value="">-- Select Business Sector --</option>
                    @foreach($sectors as $sector)
                        @if(old('business_sector_id') == $sector->id || ($loan->sector && $loan->sector->id == $sector->id))
                            <option value="{{ $sector->id }}" selected>{{ $sector->name }}</option>
                        @else
                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group {{ has_error('monthly_income') }}">
                <label for="monthly_income" class="label-control">Monthly Income</label>
                <input type="number" name="monthly_income" id="monthly_income" class="form-control"
                       placeholder="Eg. 800000" value="{{ old('monthly_income', $loan->monthly_income) }}">
            </div>

            <div class="form-group {{ has_error('purpose') }}">
                <label for="purpose" class="label-control">Further reason for Loan</label>
                <textarea placeholder="Please indicate the Client's reasons for applying for this loan"
                          name="purpose" id="purpose" class="form-control" rows="4">{{ old('purpose', $loan->purpose) }}</textarea>
            </div>

        </div>
    </div>
</div>
