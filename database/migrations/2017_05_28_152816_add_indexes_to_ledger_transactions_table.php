<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToLedgerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ledger_transactions', function (Blueprint $table) {
            $table->index(['uuid', 'branch_id', 'value_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ledger_transactions', function (Blueprint $table) {
            $table->dropIndex(['uuid', 'branch_id', 'value_date']);
        });
    }
}
