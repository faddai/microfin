<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/11/2016
 * Time: 1:51 PM
 */
?>
<div class="col-md-6">
    <div class="form-group {{ has_error('firstname') }}">
        <label for="firstname" class="col-md-3 control-label">First name</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="firstname" name="firstname"
                   value="{{ old('firstname', $client->clientable ? $client->clientable->firstname : '') }}"
                   placeholder="Client's first name">
        </div>
    </div>

    <div class="form-group">
        <label for="middlename" class="col-md-3 control-label">Middle name</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="middlename" name="middlename"
                   value="{{ old('middlename', $client->clientable ? $client->clientable->middlename : '') }}" placeholder="Client's middle name">
        </div>
    </div>

    <div class="form-group {{ has_error('lastname') }}">
        <label for="lastname" class="col-md-3 control-label">Last name</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="lastname" name="lastname"
                   value="{{ old('lastname', $client->clientable ? $client->clientable->lastname : '') }}" placeholder="Client's last name">
        </div>
    </div>

    <div class="form-group {{ has_error('gender') }}">
        <label for="gender" class="col-md-3 control-label">Gender</label>
        <div class="col-md-9">
            <select class="form-control" id="gender" name="gender">
                @foreach(['male', 'female'] as $gender)
                    @if(old('gender') === $gender || $client->clientable && $client->clientable->gender === $gender)
                        <option value="{{ $gender }}" selected>{{ ucfirst($gender) }}</option>
                    @else
                        <option value="{{ $gender }}">{{ ucfirst($gender) }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

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

    <div class="form-group {{ has_error('email') }}">
        <label for="email" class="col-md-3 control-label">Email</label>
        <div class="col-md-9">
            <input type="email" class="form-control" id="email" name="email"
                   value="{{ old('email', $client->email) }}" placeholder="Email Address">
        </div>
    </div>

    <div class="form-group {{ has_error('address') }}">
        <label for="address" class="col-md-3 control-label">Address</label>
        <div class="col-md-9">
            <textarea name="address" id="address" rows="2" class="form-control"
                      placeholder="Residential address">{{ old('address', $client->address) }}</textarea>
        </div>
    </div>

</div>
<div class="col-md-6">

    <div class='form-group {{ has_error('dob') }}'>
        <label for="dob" class="col-md-3 control-label">Date of Birth</label>
        <div class="col-md-9">
            <input type='text' class="form-control" name="dob" role="datepicker" data-date-format="yyyy-mm-dd"
                   value="{{ old('dob', $client->clientable ? $client->clientable->dob->format('Y-m-d') : '') }}"
                   placeholder="yyyy-mm-dd"/>
        </div>
    </div>

    <div class="form-group {{ has_error('relationship_manager') }}">
        <label for="relationship_manager" class="col-md-3 control-label">Relationship Manager</label>
        <div class="col-md-9">
            <select class="form-control" id="relationship_manager" name="relationship_manager"
                    role="select2" data-placeholder="-- Select Rel. Manager --">
                <option value=""></option>
                @foreach($relationshipManagers as $rm)
                    @if(old('relationship_manager') == $rm->id || $client->relationshipManager && $client->relationshipManager->id === $rm->id)
                        <option value="{{ $rm->id }}" selected>{{ $rm->getFullName() }}</option>
                    @else
                        <option value="{{ $rm->id }}">{{ $rm->getFullName() }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group {{ has_error('branch') }}">
        <label for="branch_id" class="col-md-3 control-label">Branch</label>
        <div class="col-md-9">
            <select class="form-control" id="branch_id" name="branch_id"
                    role="select2" data-placeholder="-- Select Branch --">
                <option value=""></option>
                @foreach($branches as $branch)
                    @if(old('branch_id') == $branch->id || $client->branch && $client->branch->id === $branch->id)
                        <option value="{{ $branch->id }}" selected>{{ $branch->getDisplayName() }}</option>
                    @else
                        <option value="{{ $branch->id }}">{{ $branch->getDisplayName() }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group {{ has_error('nationality') }}">
        <label for="nationality" class="col-md-3 control-label">Nationality</label>
        <div class="col-md-9">
            <select class="form-control" id="nationality" name="nationality"
                    role="select2" data-placeholder="-- Select Nationality --">
                <option value="">--</option>
                @foreach($countries as $country)
                    @if(old('nationality') == $country->alpha_2_code || $client->country &&
                    $client->country->alpha_2_code === $country->nationality || $country->alpha_2_code === 'ZM')
                        <option value="{{ $country->alpha_2_code }}" selected>{{ $country->name }}</option>
                    @else
                        <option value="{{ $country->alpha_2_code }}">{{ $country->nationality }}</option>
                    @endif
                @endforeach

            </select>
        </div>
    </div>

    @include('dashboard.clients._photo_and_signature_input')

    <div class="form-group {{ has_error('identification_type') }}">
        <label for="identification_type" class="col-md-3 control-label">ID Type</label>
        <div class="col-md-9">
            <select class="form-control" id="identification_type" name="identification_type">
                <option value="">-- Select ID type --</option>
                @foreach($identificationTypes as $key => $id_type)
                    @if(old('identification_type') == $key || $client->identification_type === $key)
                        <option value="{{ $key }}" selected>{{ $id_type }}</option>
                    @else
                        <option value="{{ $key }}">{{ $id_type }}</option>
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

    <div class="form-group {{ has_error('marital_status') }}">
        <label for="marital_status" class="col-md-3 control-label">Marital status</label>
        <div class="col-md-9">
            <select class="form-control" id="marital_status" name="marital_status">
                @foreach(trans('marital_status') as $status)
                    @if(old('marital_status') === $status || $client->clientable && $client->clientable->marital_status === $status)
                        <option value="{{ $status }}" selected>{{ ucfirst($status) }}</option>
                    @else
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group hide {{ has_error('spouse_name') }}">
        <label for="spouse_name" class="col-md-3 control-label">Spouse name</label>
        <div class="col-md-9">
            <input type="text" class="form-control" id="spouse_name" name="spouse_name"
                   value="{{ old('spouse_name', $client->clientable ? $client->clientable->spouse_name : '') }}"
                   placeholder="Name of spouse">
        </div>
    </div>

</div>

@push('more_scripts')
<script>
    var $inputSpouseName = $('input[name="spouse_name"]'),
        $maritalStatus = $('select[name="marital_status"]');

    if ($maritalStatus.val() === 'married') {
        $inputSpouseName.parents('.form-group').removeClass('hide');
    }

    $maritalStatus.change(function (e) {
        var selected = $(this).val();

        if (selected === 'married') {
            $inputSpouseName.parents('.form-group').removeClass('hide');
        } else {
            $inputSpouseName.parents('.form-group').addClass('hide');
        }
    })
</script>
@endpush