<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 19/11/2016
 * Time: 6:57 PM
 */
?>
<div class="guarantor-container" data-row-id="{{$index}}">
    @if ($index > 0)
        <hr/>
    @endif
    <div class="form-group pull-in clearfix">
        @if ($guarantor && $guarantor->id)
            <input type="hidden" name="guarantors[{{$index}}][guarantor_id]" value="{{$guarantor->id}}">
        @endif
        <div class="col-md-5 m-b {{$errors->has("guarantors.{$index}.name") ? 'has-error' : ''}}">
            <label>Name of Guarantor</label>
            <input type="text" class="form-control" name="guarantors[{{$index}}][name]"
                   value="{{old("guarantors.{$index}.name", $guarantor ? $guarantor->name : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("guarantors.{$index}.name"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("guarantors.{$index}.name") }}</strong></span>
            @endif
        </div>
        <div class="col-md-5 {{$errors->has("guarantors.{$index}.relationship") ? 'has-error' : ''}}">
            <label>Relationship</label>
            <input type="text" class="form-control" name="guarantors[{{$index}}][relationship]"
                   value="{{old("guarantors.{$index}.relationship", $guarantor ? $guarantor->relationship : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("guarantors.{$index}.relationship"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("guarantors.{$index}.relationship") }}</strong></span>
            @endif
        </div>
        @if ($index == 0)
            <div class="col-md-2 m-b">
                <button type="button" class="btn btn-info" id="add-guarantor" style="margin-top:24px">
                    <i class="fa fa-plus"></i> Add another
                </button>
            </div>
        @else
            <div class="col-md-2 m-b">
                <button type="button" class="btn btn-danger remove-guarantor" style="margin-top:24px">Remove Guarantor
                </button>
            </div>
        @endif
    </div>
    <div class="form-group pull-in clearfix">
        <div class="col-md-5 {{$errors->has("guarantors.{$index}.work_phone") ? 'has-error' : ''}}">
            <label>Phone number (Work)</label>
            <input type="tel" class="form-control" name="guarantors[{{$index}}][work_phone]"
                   value="{{old("guarantors.{$index}.work_phone", $guarantor ? $guarantor->work_phone : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("guarantors.{$index}.work_phone"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("guarantors.{$index}.work_phone") }}</strong></span>
            @endif
        </div>
        <div class="col-md-3 {{$errors->has("guarantors.{$index}.personal_phone") ? 'has-error' : ''}}">
            <label>Phone number (Mobile)</label>
            <input type="tel" class="form-control" name="guarantors[{{$index}}][personal_phone]"
                   value="{{old("guarantors.{$index}.personal_phone", $guarantor ? $guarantor->personal_phone : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("guarantors.{$index}.personal_phone"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("guarantors.{$index}.personal_phone") }}</strong></span>
            @endif
        </div>
        <div class="col-md-2 {{$errors->has("guarantors.{$index}.years_known") ? 'has-error' : ''}}">
            <label>No. of Years Known</label>
            <input type="number" class="form-control" name="guarantors[{{$index}}][years_known]"
                   value="{{old("guarantors.{$index}.years_known", $guarantor ? $guarantor->years_known : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("guarantors.{$index}.years_known"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("guarantors.{$index}.years_known") }}</strong></span>
            @endif
        </div>
    </div>
    <div class="form-group pull-in clearfix">
        <div class="col-md-5 {{$errors->has("guarantors.{$index}.employer") ? 'has-error' : ''}}">
            <label>Employer</label>
            <input type="text" class="form-control" name="guarantors[{{$index}}][employer]"
                   value="{{old("guarantors.{$index}.employer", $guarantor ? $guarantor->employer : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("guarantors.{$index}.employer"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("guarantors.{$index}.employer") }}</strong></span>
            @endif
        </div>
        <div class="col-md-5 {{$errors->has("guarantors.{$index}.job_title") ? 'has-error' : ''}}">
            <label>Job Role</label>
            <input type="text" class="form-control" name="guarantors[{{$index}}][job_title]"
                   value="{{old("guarantors.{$index}.job_title", $guarantor ? $guarantor->job_title : '')}}">
            <!-- Validation errors -->
            @if ($errors->has("guarantors.{$index}.job_title"))
                <span class="help-block m-b-none"><strong>{{ $errors->first("guarantors.{$index}.job_title") }}</strong></span>
            @endif
        </div>
    </div>
</div>

<script type="text/x-custom-template" id="guarantor-template">
    <?php /* Guarantor details template */ ?>
    <div class="guarantor-container">
        <hr/>
        <div class="form-group pull-in clearfix">
            <div class="col-md-5 m-b">
                <label>Name of Guarantor</label>
                <input type="text" class="form-control guarantor-name">
            </div>
            <div class="col-md-5">
                <label>Relationship</label>
                <input type="text" class="form-control guarantor-relationship">
            </div>
            <div class="col-md-2 m-b">
                <button type="button" class="btn btn-danger remove-guarantor" style="margin-top:24px">Delete
                    Guarantor
                </button>
            </div>
        </div>
        <div class="form-group pull-in clearfix">
            <div class="col-md-5">
                <label>Phone number (Work)</label>
                <input type="tel" class="form-control guarantor-tel-work">
            </div>
            <div class="col-md-3">
                <label>Phone number (Mobile)</label>
                <input type="tel" class="form-control guarantor-tel-mobile">
            </div>
            <div class="col-md-2">
                <label>Number of Years Known</label>
                <input type="number" class="form-control guarantor-years-known">
            </div>
        </div>
        <div class="form-group pull-in clearfix">
            <div class="col-md-5">
                <label>Employer</label>
                <input type="text" class="form-control guarantor-employer">
            </div>
            <div class="col-md-5">
                <label>Job Role</label>
                <input type="text" class="form-control guarantor-job-role">
            </div>
        </div>
    </div>
</script>
