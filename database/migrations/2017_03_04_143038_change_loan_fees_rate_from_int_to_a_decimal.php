<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLoanFeesRateFromIntToADecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_fees', function (Blueprint $table) {
            $table->decimal('rate')->default(0.0)->change();
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
            $table->unsignedInteger('rate')->default(0.0)->change();
        });
    }
}
