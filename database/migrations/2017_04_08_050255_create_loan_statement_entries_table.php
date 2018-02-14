<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanStatementEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_statement_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('dr', 16)->default(0);
            $table->decimal('cr', 16)->default(0);
            $table->decimal('balance', 16)->default(0);
            $table->string('narration');
            $table->unsignedInteger('loan_statement_id');
            $table->timestamp('value_date')->useCurrent();
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
        Schema::dropIfExists('loan_statement_entries');
    }
}
