<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->decimal('dr', 16, 8)->default(0);
            $table->decimal('cr', 16, 8)->default(0);
            $table->string('narration')->nullable();
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('branch_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('ledger_id')->nullable();
            $table->string('receipt')->nullable();
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
        Schema::dropIfExists('client_transactions');
    }
}
