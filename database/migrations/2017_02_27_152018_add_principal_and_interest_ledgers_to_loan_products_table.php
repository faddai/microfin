<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/02/2017
 * Time: 15:20
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrincipalAndInterestLedgersToLoanProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->unsignedInteger('principal_ledger_id')->nullable();
            $table->unsignedInteger('interest_ledger_id')->nullable();
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
            $table->dropColumn('principal_ledger_id', 'interest_ledger_id');
        });
    }
}
