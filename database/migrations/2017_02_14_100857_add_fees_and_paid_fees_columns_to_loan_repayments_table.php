<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeesAndPaidFeesColumnsToLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->decimal('fees', 16)->default(0);
            $table->decimal('paid_fees', 16)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn('fees', 'paid_fees');
        });
    }
}