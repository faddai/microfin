<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusPaidPrincipalPaidInterestColumnsToLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->string('status', 20)->nullable();
            $table->decimal('paid_interest')->default(0);
            $table->decimal('paid_principal')->default(0);
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
            $table->dropColumn(['status', 'paid_principal', 'paid_interest']);
        });
    }
}
