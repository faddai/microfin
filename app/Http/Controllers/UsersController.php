<?php

namespace App\Http\Controllers;

use App\Entities\Role;
use App\Entities\User;
use App\Http\Requests\AddUserFormRequest;
use App\Jobs\AddUserJob;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param AddUserFormRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddUserFormRequest $request)
    {
        try {
            $this->dispatch(new AddUserJob($request));
        } catch (\Exception $e) {
            logger()->error("User couldn't be added", ['error' => $e->getMessage()]);

            flash()->error('User could not be created');
        }

        flash()->success('User created. An email notification has been sent to the user to set a password.');

        return redirect(route_with_hash('settings.index', '#users'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $roles = Role::all('id', 'display_name');

        return view('dashboard.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AddUserFormRequest|Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function update(AddUserFormRequest $request, User $user)
    {
        try {
            $this->dispatch(new AddUserJob($request, $user));
        } catch (\Exception $e) {
            logger()->error('User update failed', ['error' => $e->getMessage()]);

            flash()->error('User could not be updated');

            return redirect(route_with_hash('settings.index', '#users'))->withInput();
        }

        flash()->success('User details updated');

        return redirect(route_with_hash('settings.index', '#users'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        try {
            $user->delete() && cache()->forget('users');
        } catch (\Exception $e) {
            logger()->error('User could not be deleted', ['error' => $e]);

            flash()->error('User could not be deleted');
        }

        flash()->success('User successfully deleted');

        return redirect(route_with_hash('settings.index', '#users'));
    }

    public function suspend(User $user)
    {
        return view('dashboard.users.suspend', compact('user'));
    }
}
