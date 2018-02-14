<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberOfRepaymentsPerMonthColumnToRepaymentPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repayment_plans', function (Blueprint $table) {
            $table->integer('number_of_repayments_per_month')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repayment_plans', function (Blueprint $table) {
            $table->dropColumn('number_of_repayments_per_month');
        });
    }
}
