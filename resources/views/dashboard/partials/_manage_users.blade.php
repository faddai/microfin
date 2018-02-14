<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 31/10/2016
 * Time: 01:25
 */
?>
@if($users->count())
    <div class="table-responsive">
        <table class="table table-striped table-condensed">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role (s)</th>
                <th>Branch</th>
                <th>Last login</th>
                <th width="110px">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email or 'N/A'}}</td>
                    <td>{{ $user->roles->count() ? $user->roles->implode('display_name', ', ') : 'N/A' }}</td>
                    <td>{{ $user->branch->name or 'N/A'}}</td>
                    <td>{{ $user->last_login ? $user->last_login->diffForHumans() : 'N/A'}}</td>
                    <td>
                        <div class="btn-group" role="button">
                            <a href="{{ route('users.edit', compact('user')) }}" class="btn btn-default btn-sm"
                               data-toggle="tooltip" title="Edit user account" data-placement="bottom">
                                <i class="fa fa-pencil"></i>
                            </a>
                            @if($user->isActive())
                                <a href="{{ route('users.suspend', compact('user')) }}" class="btn btn-warning btn-sm"
                                   data-toggle="tooltip" data-placement="top" title="Suspend account">
                                    <i class="fa fa-lock"></i>
                                </a>
                            @else
                                <a href="{{ route('users.update', compact('user')) }}" title="Re-activate account"
                                   class="btn btn-warning btn-sm js-activate-user" data-placement="top"
                                   data-toggle="tooltip">
                                    <i class="fa fa-unlock"></i>
                                </a>
                            @endif
                            <a href="#delete-user-modal" data-toggle="modal" class="btn btn-danger btn-sm"
                               data-id="{{ $user->id }}" data-name="{{ $user->getFullName() }}">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center">
        <i class="fa fa-list-ul fa-5x"></i>
        <p>You haven't added any users yet.</p>
    </div>
@endif

<div id="add-user-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <h4 class="modal-title">Add user</h4>
            </div>
            <form action="{{ route('users.store') }}" method="POST" name="add-user-form">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" id="name" class="form-control" name="name" placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label for="email" class="label-control">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="john@betternowfinance.co.zm">
                    </div>

                    <div class="form-group">
                        <label for="branch" class="label-control">Branch</label>
                        <select class="form-control" id="branch" name="branch_id">
                            <option value="">-- Choose branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->getDisplayName() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group well">
                        <p>Assign role(s) to the user to enable him/her carry out his job functions</p>
                        @foreach($roles as $role)

                            <label for="role_{{ $role->id }}" class="control-label m-r-md">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                       id="role_{{ $role->id }}"> {{ $role->display_name }}
                            </label>

                        @endforeach
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save user
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<div id="delete-user-modal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="delete-user-form" action="{{ route('users.delete', ['user' => ':id']) }}" method="POST">
                <div class="modal-body text-center">
                    <p><i class="fa fa-exclamation-triangle fa-5x text-danger"></i></p>

                    <p>If continued, <strong class="username"></strong> won't be able to log into the application
                        again.</p>
                    <p><strong>NB: </strong>This action can't be reversed.</p>

                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="delete">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash-o"></i> Delete <strong class="username"></strong>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="js-activate-user-form" action="{{ route('users.update', ['user' => ':id']) }}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="_method" value="put">
    <input type="hidden" name="is_active" value="1">
</form>