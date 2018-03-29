<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

use App\Entities\Role;
use App\Entities\User;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    private $user;

    public function setUp()
    {
        parent::setUp();

        auth()->login(factory(User::class)->create());

        $this->user = factory(User::class)->create();
    }

    public function test_add_user()
    {
        $params = [
            'name' => faker()->name,
            'branch_id' => factory(\App\Entities\Branch::class)->create()->id,
            'roles' => [Role::firstOrCreate(['display_name' => Role::ACCOUNT_MANAGER])->id],
            'email' => faker()->unique()->email,
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234'
        ];

        $this->post('users/store', $params)
            ->assertStatus(302)
            ->assertRedirect(route_with_hash('settings.index', '#users'))
            ->assertSessionHas(
                'flash_notification.message',
                'User created. An email notification has been sent to the user to set a password.'
            );
    }

    /**
     * Create 2 roles and make a PUT request to assign them
     * to the user
     */
    public function test_add_user_roles()
    {
        $roles = [
            Role::firstOrCreate(['display_name' => Role::ACCOUNT_MANAGER])->id,
            Role::firstOrCreate(['display_name' => Role::CASHIER])->id
        ];

        $this->put('users/'. $this->user->id, compact('roles'))
            ->assertRedirect(route_with_hash('settings.index', '#users'))
            ->assertSessionHas('flash_notification.message', 'User details updated');

        self::assertCount(2, $this->user->roles);
    }

    /**
     * Add a user with 2 roles
     * Replace these roles with a new one
     */
    public function test_update_user_roles()
    {
        $roles = [
            Role::firstOrCreate(['display_name' => Role::ACCOUNT_MANAGER])->id,
            Role::firstOrCreate(['display_name' => Role::CASHIER])->id
        ];

        $this->user->syncRoles($roles);

        $branchManager = Role::firstOrCreate(['display_name' => Role::BRANCH_MANAGER])->id;

        $this->put('users/'. $this->user->id, ['roles' => [$branchManager]])
            ->assertRedirect(route_with_hash('settings.index', '#users'))
            ->assertSessionHas('flash_notification.message', 'User details updated');

        self::assertCount(1, $this->user->roles);
    }

    public function test_that_user_can_be_suspended()
    {
        $params = ['is_active' => 0];

        $this->put('users/'. $this->user->id, $params)
            ->assertRedirect(route_with_hash('settings.index', '#users'))
            ->assertSessionHas('flash_notification.message', 'User details updated');

        self::assertFalse($this->user->fresh()->isActive());
    }

    public function test_that_user_can_be_reactivated()
    {
        $params = ['is_active' => 1];

        $this->put('users/'. $this->user->id, $params)
            ->assertRedirect(route_with_hash('settings.index', '#users'))
            ->assertSessionHas('flash_notification.message', 'User details updated');

        self::assertTrue($this->user->fresh()->isActive());
    }
}
