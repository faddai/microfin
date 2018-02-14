<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollateralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collaterals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('client_id')->nullable();
            $table->timestamps();

            $table->foreign('client_id', 'fk_collaterals_client_id')->references('id')->on('clients')
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
        Schema::dropIfExists('collaterals');
    }
}
