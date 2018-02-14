<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndividualClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('individual_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string("firstname", 60)->index()->nullable();
            $table->string("lastname", 60)->index()->nullable();
            $table->string("middlename", 60)->nullable();
            $table->date("dob")->nullable();
            $table->string("gender")->nullable();
            $table->string("photo")->nullable();
            $table->string("signature")->nullable();
            $table->string("marital_status")->nullable();
            $table->string("spouse_name")->nullable();
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
        Schema::dropIfExists('individual_clients');
    }
}
