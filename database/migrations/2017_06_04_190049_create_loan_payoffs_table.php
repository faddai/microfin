<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanPayoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_payoffs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status', 10);
            $table->text('remarks')->nullable();
            $table->text('decline_reason')->nullable();
            $table->decimal('amount', 16);
            $table->decimal('interest', 16)->nullable();
            $table->decimal('principal', 16)->nullable();
            $table->decimal('fees', 16)->nullable();
            $table->decimal('penalty', 16)->default(0);
            $table->unsignedInteger('loan_id');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('declined_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('loan_payoffs');
    }
}
