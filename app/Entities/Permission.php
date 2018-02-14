<?php

namespace App\Entities;

use Laratrust\LaratrustPermission;

class Permission extends LaratrustPermission
{
    const VIEW_USER = 'view.user';
    const CREATE_USER = 'create.user';
    const DELETE_USER = 'delete.user';
    const UPDATE_USER = 'update.user';

    const VIEW_CLIENT = 'view.client';
    const CREATE_CLIENT = 'create.client';
    const DELETE_CLIENT = 'delete.client';
    const UPDATE_CLIENT = 'update.client';

    const CREATE_LOAN = 'create.loan';
    const APPROVE_LOAN = 'approve.loan';
    const VIEW_LOAN = 'view.loan';
    const REJECT_LOAN = 'reject.loan';
    const DISBURSE_LOAN = 'disburse.loan';
    const RESTRUCTURE_LOAN = 'restructure.loan';
    const APPROVE_LOAN_PAYOFF = 'payoff.loan';

    const CREATE_DEPOSIT = 'create.deposit';
    const VIEW_DEPOSIT = 'create.deposit';
    const REVERSE_DEPOSIT = 'create.deposit';
    const APPROVE_TRANSACTION_REVERSAL = 'reverse.transaction';

    protected $fillable = ['name', 'description', 'display_name'];
}
