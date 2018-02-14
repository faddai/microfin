<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanRepaymentCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayment_collections', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('loan_repayment_id');
            $table->timestamp('collected_at')->nullable();
            $table->unsignedInteger('collected_by')->nullable();
            $table->decimal('amount', 16);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_repayment_collections');
    }
}
