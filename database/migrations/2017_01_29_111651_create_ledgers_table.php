<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('code')->unique();
            $table->unsignedInteger('category_id');
            $table->boolean('is_bank_or_cash')->default(0);
            $table->timestamps();

            $table->foreign('category_id', 'fk_ledgers_category_id')->references('id')->on('ledger_categories')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledgers');
    }
}
