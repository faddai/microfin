<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterestIncomeLedgerIdColumnToLoanProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->unsignedInteger('interest_income_ledger_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn('interest_income_ledger_id');
        });
    }
}
