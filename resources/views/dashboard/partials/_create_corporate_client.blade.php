<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 17/11/2016
 * Time: 10:06
 */
?>
<div class="col-md-6">
    <div class="form-group {{ has_error('company_name') }}">
        <label for="company_name" class="col-md-3 control-label">Company name</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="company_name" name="company_name"
                   value="{{ old('company_name', $client->clientable ? $client->clientable->company_name : '') }}"
                   placeholder="Company name">
        </div>
    </div>

    <div class="form-group {{ has_error('business_registration_number') }}">
        <label for="business_registration_number" class="col-md-3 control-label">Registration Number</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="business_registration_number" name="business_registration_number"
                   value="{{ old('business_registration_number', $client->clientable ? $client->clientable->business_registration_number : '') }}"
                   placeholder="Business Registration Number">
        </div>
    </div>

    <div class="form-group {{ has_error('date_of_incorporation') }}">
        <label for="date_of_incorporation" class="col-md-3 control-label">Date of Incorporation</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="date_of_incorporation" name="date_of_incorporation"
                   value="{{ old('date_of_incorporation', $client->clientable ? $client->clientable->date_of_incorporation : '') }}"
                   placeholder="yyyy-mm-dd" role="datepicker" data-date-format="yyyy-mm-dd">
        </div>
    </div>

    <div class="form-group {{ has_error('company_type') }}">
        <label for="company_type" class="col-md-3 control-label">Company type</label>
        <div class="col-md-9">
            <select name="company_type" id="company_type" class="form-control">
                <option value="">-- Select Company type --</option>
                @foreach(trans('company_ownership_types') as $companyType)
                    <option value="{{ $companyType }}" {{ $client->clientable ? ($client->clientable->company_type === $companyType ? 'selected' : '') : '' }}>{{ $companyType }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group {{ has_error('company_email') }}">
        <label for="email" class="col-md-3 control-label">Company email</label>
        <div class="col-md-9">
            <input type="email" class="form-control" id="email" name="email"
                   value="{{ old('email', $client->email) }}" placeholder="Email Address">
        </div>
    </div>


    <div class="form-group {{ has_error('nationality') }}">
        <label for="nationality" class="col-md-3 control-label left">Country of Operation</label>
        <div class="col-md-9">
            <select class="form-control" id="nationality" name="nationality"
                    role="select2" data-placeholder="-- Select Country of Operation --">
                <option value="">--</option>
                @foreach($countries as $country)
                    @if(old('nationality') === $country->alpha_2_code)
                        <option value="{{ $country->alpha_2_code }}" selected>{{ $country->name }}</option>
                    @else
                        <option value="{{ $country->alpha_2_code }}" {{ $country->alpha_2_code === 'ZM' ? 'selected' : '' }}>{{ $country->name }}</option>
                    @endif
                @endforeach

            </select>
        </div>
    </div>

    @include('dashboard.clients._photo_and_signature_input')

</div>
<div class="col-md-6">
    <div class="form-group {{ has_error('phone1') }}">
        <label for="phone1" class="col-md-3 control-label">Phone #1</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="phone1" name="phone1"
                   value="{{ old('phone1', $client->phone1) }}" placeholder="Phone number">
        </div>
    </div>

    <div class="form-group">
        <label for="phone2" class="col-md-3 control-label">Phone #2</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="phone2" name="phone2"
                   value="{{ old('phone2', $client->phone2) }}" placeholder="Phone number">
        </div>
    </div>

    <div class="form-group {{ has_error('address') }}">
        <label for="address" class="col-md-3 control-label">Address</label>
        <div class="col-md-9">
            <textarea name="address" id="address" rows="2" class="form-control"
                      placeholder="Business address">{{ old('address', $client->address) }}</textarea>
        </div>
    </div>

    <div class="form-group {{ has_error('relationship_manager') }}">
        <label for="relationship_manager" class="col-md-3 control-label">Relationship Manager</label>
        <div class="col-md-9">
            <select class="form-control" id="relationship_manager" name="relationship_manager"
                    role="select2" data-placeholder="-- Select Rel. Manager --">
                <option value=""></option>
                @foreach($relationshipManagers as $rm)
                    @if(old('relationship_manager') == $rm->id)
                        <option value="{{ $rm->id }}" selected>{{ $rm->getFullName() }}</option>
                    @else
                        <option value="{{ $rm->id }}"
                                {{ isset($client->relationshipManager) && ($client->relationshipManager->id == $rm->id) ? 'selected' : '' }}>
                            {{ $rm->getFullName() }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group {{ has_error('branch_id') }}">
        <label for="branch_id" class="col-md-3 control-label">Branch</label>
        <div class="col-md-9">
            <select class="form-control" id="branch_id" name="branch_id"
                    role="select2" data-placeholder="-- Select Branch --">
                <option value=""></option>
                @foreach($branches as $branch)
                    @if(old('branch_id') == $branch->id)
                        <option value="{{ $branch->id }}" selected>{{ $branch->getDisplayName() }}</option>
                    @else
                        <option value="{{ $branch->id }}" {{ isset($client->branch) && ($client->branch->id == $branch->id) ? 'selected' : '' }}>
                            {{ $branch->getDisplayName() }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group {{ has_error('identification_type') }}">
        <label for="identification_type" class="col-md-3 control-label">ID Type</label>
        <div class="col-md-9">
            <select class="form-control" id="identification_type" name="identification_type">
                <option value="">-- Select ID type --</option>
                @foreach($identificationTypes as $key => $id_type)
                    @if(old('identification_type') === $key)
                        <option value="{{ $key }}" selected>{{ $id_type }}</option>
                    @else
                        <option value="{{ $key }}" {{ $client->identification_type === $key ? 'selected' : '' }}>{{ $id_type }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group {{ has_error('identification_number') }}">
        <label for="identification_number" class="col-md-3 control-label">ID No.</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="identification_number" name="identification_number"
                   value="{{ old('identification_number', $client->identification_number) }}"
                   placeholder="Identification number">
        </div>
    </div>

</div>

@push('more_scripts')
<script type="text/javascript"></script>
@endpush
