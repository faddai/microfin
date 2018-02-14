<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/10/2016
 * Time: 21:35
 */
?>
@if($branches->count())
    <div class="table-responsive">
        <table class="table table-striped table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Location</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($branches as $branch)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->code or 'N/A' }}</td>
                    <td>{{ $branch->location or 'N/A' }}</td>
                    <td>{{ $branch->status ? 'Operational' : 'Not Operational' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center">
        <i class="fa fa-list-ul fa-5x"></i>
        <p>You haven't added any branches yet.</p>
    </div>
@endif

<div id="add-branch-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-branch-form" action="{{ route('branches.store') }}" method="POST">
                <div class="modal-body">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="name" class="control-label">Branch name</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label for="code" class="control-label">Branch code</label>
                        <input type="text" class="form-control" name="code" id="code" value="{{ old('code') }}">
                    </div>

                    <div class="form-group">
                        <label for="location" class="control-label">Branch location</label>
                        <input type="text" class="form-control" name="location" id="location" value="{{ old('location') }}">
                    </div>

                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-trash-o"></i> Save branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>