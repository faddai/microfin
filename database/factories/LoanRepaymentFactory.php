<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 02/12/2016
 * Time: 15:37
 */
use App\Entities\Loan;
use App\Entities\LoanRepayment;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(LoanRepayment::class, function () {

    $loan = factory(Loan::class)->create();

    return [
        'loan_id' => $loan->id,
        'amount' => faker()->randomNumber(4),
        'payment_method' => 'cash',
        'has_been_paid' => 0,
    ];
});