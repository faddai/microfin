<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsToLoanFeesPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_fees', function (Blueprint $table) {
            $table->unsignedInteger('fee_id')->nullable();
            $table->unsignedInteger('rate')->default(0.0);

            // assign loan_id an appropriate data type
            $table->unsignedInteger('loan_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_fees', function (Blueprint $table) {
            $table->dropColumn('fee_id', 'rate');
        });
    }
}
