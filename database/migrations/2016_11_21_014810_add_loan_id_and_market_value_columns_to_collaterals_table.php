<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoanIdAndMarketValueColumnsToCollateralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collaterals', function (Blueprint $table) {
            $table->unsignedInteger('loan_id')->nullable();
            $table->double('market_value')->nullable(); // market value of the collateral

            $table->foreign('loan_id', 'fk_collaterals_loan_id')->references('id')->on('loans')
                ->onUpdate('cascade')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collaterals', function (Blueprint $table) {
            $table->dropForeign('fk_collaterals_loan_id');
            $table->dropColumn('loan_id', 'market_value');
        });
    }
}
