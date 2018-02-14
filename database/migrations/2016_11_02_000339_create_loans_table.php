<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->unsignedInteger('tenure_id')->nullable();
            $table->unsignedInteger('repayment_plan_id')->nullable();
            $table->unsignedInteger('credit_officer')->nullable();
            $table->string('loan_size', 30)->nullable();
            $table->text('purpose')->nullable();
            $table->double('amount');
            $table->double('rate')->nullable();
            $table->unsignedInteger('zone_id')->nullable();
            $table->string('grace_period')->nullable();
            $table->unsignedInteger('loan_type_id')->nullable();
            $table->string('age_group')->nullable();
            $table->double('monthly_income')->nullable();
            $table->string('number', 20)->nullable();
            $table->timestamps();

            $table->foreign('client_id', 'fk_loans_client_id')->references('id')->on('clients')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('tenure_id', 'fk_loans_tenure_id')->references('id')->on('tenures')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('credit_officer', 'fk_loans_credit_officer')->references('id')->on('users')
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
        Schema::dropIfExists('loans');
    }
}
