<?php

namespace App\Entities;

use Laratrust\LaratrustRole;

class Role extends LaratrustRole
{
    const ADMINISTRATOR = 'Administrator';
    const RELATIONSHIP_MANAGER = 'Relationship Manager';
    const BRANCH_MANAGER = 'Branch Manager';
    const CREDIT_OFFICER = 'Credit Officer';
    const SUPERVISOR = 'Supervisor';
    const CASHIER = 'Cashier';
    const ACCOUNT_MANAGER = 'Account Manager';

    protected $fillable = ['name', 'display_name', 'description'];
}
