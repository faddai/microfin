<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/10/2016
 * Time: 21:41
 */
?>
@if($zones->count())
    <div class="table-responsive">
        <table class="table table-striped table-condensed">
            <thead>
            <tr>
                <th width="10%">#</th>
                <th width="60%">Name</th>
                <th width="30%">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($zones as $zone)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $zone->name }}</td>
                    <td>
                        <a href="#edit-zone-modal" data-toggle="modal" class="btn btn-default btn-sm"
                           data-zone-id="{{ $zone->id }}" data-zone-name="{{ $zone->name }}">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <a href="#delete-zone-modal" data-toggle="modal" class="btn btn-danger btn-sm"
                           data-zone-id="{{ $zone->id }}">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center">
        <i class="fa fa-list-ul fa-5x"></i>
        <p>You haven't added any Zone yet.</p>
    </div>
@endif

<div id="edit-zone-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <h4 class="modal-title">Edit zone</h4>
            </div>
            <form action="{{ route('settings.zones.store', ['user' => ':id']) }}" method="POST" name="edit-zone-form">
                {{ csrf_field() }}
                <input type="hidden" value="put" name="_method">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" id="name" class="form-control" name="name" value="{{ old('name') }}"
                               placeholder="Name of Zone">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-zone-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form name="delete-zone-form" action="" method="POST">
                <div class="modal-body text-center">
                    <p>
                        <i class="fa fa-exclamation-triangle fa-5x text-danger"></i>
                    </p>

                    <p>Are you sure you want to delete this zone? This action can't be reversed.</p>
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="delete">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash-o"></i> Yes, delete Zone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>