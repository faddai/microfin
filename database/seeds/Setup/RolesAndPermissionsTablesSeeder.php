<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 12/11/2016
 * Time: 10:34 AM
 */

namespace Setup;

use App\Entities\Permission;
use App\Entities\Role;
use Illuminate\Database\Seeder;


class RolesAndPermissionsTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rolesAndPermissions = [
            [
                'role' => Role::ADMINISTRATOR,
                'permissions' => [
                    Permission::VIEW_USER,
                    Permission::CREATE_USER,
                    Permission::DELETE_USER,
                    Permission::UPDATE_USER,
                ]
            ],
            [
                'role' => Role::RELATIONSHIP_MANAGER,
                'permissions' => [
                    Permission::VIEW_CLIENT,
                    Permission::UPDATE_CLIENT,
                    Permission::CREATE_CLIENT,
                ]
            ],
            [
                'role' => Role::BRANCH_MANAGER,
                'permissions' => [
                    Permission::APPROVE_LOAN,
                    Permission::APPROVE_LOAN_PAYOFF,
                    Permission::APPROVE_TRANSACTION_REVERSAL,
                    Permission::RESTRUCTURE_LOAN,
                    Permission::DISBURSE_LOAN
                ]
            ],
            [
                'role' => Role::CREDIT_OFFICER,
                'permissions' => [
                    Permission::CREATE_LOAN,
                    Permission::VIEW_LOAN,
                ]
            ],
            [
                'role' => Role::SUPERVISOR,
                'permissions' => [
                    Permission::APPROVE_LOAN,
                    Permission::VIEW_LOAN,
                ]
            ],
            [
                'role' => Role::CASHIER,
                'permissions' => [
                    Permission::CREATE_DEPOSIT,
                    Permission::VIEW_DEPOSIT,
                    Permission::VIEW_CLIENT,
                    Permission::VIEW_LOAN
                ]
            ],
            [
                'role' => Role::ACCOUNT_MANAGER,
                'permissions' => [
                    Permission::CREATE_CLIENT,
                ]
            ]
        ];

        foreach ($rolesAndPermissions as $rolePermissions) {

            $role = Role::firstOrCreate([
                'name' => str_slug($rolePermissions['role']),
                'display_name' => $rolePermissions['role']
            ]);

            if ($this->roleHasPermissions($rolePermissions)) {
                $this->createPermissionsAndAssignToRole($role, $rolePermissions);
            }

        }

        cache()->forget('roles'); // bust roles from cache
    }

    private function roleHasPermissions($rolePermissions)
    {
        return array_key_exists('permissions', $rolePermissions) && !empty($rolePermissions['permissions']);
    }

    /**
     * Add permissions to the just created role
     *
     * @param $role
     * @param $rolePermissions
     * @param $permissionIds
     */
    private function createPermissionsAndAssignToRole($role, $rolePermissions = []): void
    {
        $permissionIds = [];

        foreach ($rolePermissions['permissions'] as $permission) {
            $_permission = Permission::firstOrCreate(['name' => $permission]);
            $permissionIds[] = $_permission->id;
        }

        $role->permissions()->sync($permissionIds);
    }
}