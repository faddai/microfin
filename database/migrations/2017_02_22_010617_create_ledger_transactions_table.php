<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->unsignedInteger('branch_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamp('value_date')->useCurrent();
            $table->unsignedInteger('loan_id')->nullable();
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
        Schema::dropIfExists('ledger_transactions');
    }
}
