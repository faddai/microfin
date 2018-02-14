<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/10/2016
 * Time: 21:22
 */
?>
<div class="row m-b">

    <div class="col-md-3 m-b">
        <small class="text-primary">Name</small>
        <div class="font-bold">{{ $authUser->name }}</div>
    </div>

    <div class="col-md-3 m-b">
        <small class="text-primary">Email Address</small>
        <div class="font-bold">{{ $authUser->email or 'N/A'}}</div>
    </div>

    <div class="col-md-3 m-b">
        <small class="text-primary">Role</small>
        <div class="font-bold">{{ $authUser->roles->count() ? $authUser->roles->first()->display_name : 'N/A' }}</div>
    </div>

    <div class="col-md-3 m-b">
        <small class="text-primary">Branch</small>
        <div class="font-bold">{{ isset($authUser->branch) && ! is_null($authUser->branch) ? $authUser->branch->name : 'N/A' }}</div>
    </div>

</div>

<hr>

<div class="row">
    <div class="col-md-12">
        <h4>Audit log</h4>
        <p>There no activities here</p>
    </div>
</div>