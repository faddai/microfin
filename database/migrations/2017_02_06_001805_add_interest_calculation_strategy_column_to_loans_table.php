<?php

use App\Entities\Loan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInterestCalculationStrategyColumnToLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->enum(
                'interest_calculation_strategy',
                [Loan::REDUCING_BALANCE_STRATEGY, Loan::STRAIGHT_LINE_STRATEGY]
            )->default(Loan::REDUCING_BALANCE_STRATEGY);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('interest_calculation_strategy');
        });
    }
}
