<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('narration');
            $table->decimal('dr', 16)->default(0);
            $table->decimal('cr', 16)->default(0);
            $table->unsignedInteger('ledger_id');
            $table->uuid('ledger_transaction_id')->nullable();
            $table->unsignedInteger('branch_id')->nullable(); // purposely for entries without a transaction
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
        Schema::dropIfExists('ledger_entries');
    }
}
