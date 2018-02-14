<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPaidUpfrontAndTypeColumnsToLoanFeesPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_fees', function (Blueprint $table) {
            $table->boolean('is_paid_upfront')->nullable(); // Overridden is_paid_upfront during loan creation
            $table->string('type', 15)->nullable();
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
            $table->dropColumn('is_paid_upfront', 'type');
        });
    }
}
