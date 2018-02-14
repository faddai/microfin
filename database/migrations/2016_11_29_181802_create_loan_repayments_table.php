<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('loan_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->double('amount')->nullable();
            $table->double('interest')->nullable();
            $table->double('principal')->nullable();
            $table->string('payment_method', 20)->nullable();
            $table->boolean('has_been_paid')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('repayment_timestamp')->nullable();
            $table->timestamps();

            $table->foreign('loan_id', 'fk_loan_repayments_loan_id')->references('id')->on('loans')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('user_id', 'fk_loan_repayments_user_id')->references('id')->on('users')
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
        Schema::dropIfExists('loan_repayments');
    }
}
